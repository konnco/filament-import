<?php

namespace Konnco\FilamentImport;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Concerns\HasActionMutation;
use Maatwebsite\Excel\Concerns\Importable;

class Import
{
    use Importable;
    use HasActionMutation;

    protected string $spreadsheet;

    protected Collection $fields;

    protected array $formSchemas;

    protected string|Model $model;

    protected string $disk = 'local';

    protected bool $shouldSkipHeader = false;

    protected bool $shouldMassCreate = true;

    public static function make(string $spreadsheetFilePath): self
    {
        return (new self)
            ->spreadsheet($spreadsheetFilePath);
    }

    public function fields(Collection $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    public function formSchemas(array $formSchemas): static
    {
        $this->formSchemas = $formSchemas;

        return $this;
    }

    public function spreadsheet($spreadsheet): static
    {
        $this->spreadsheet = $spreadsheet;

        return $this;
    }

    public function model(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function disk($disk = 'local'): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function skipHeader(bool $shouldSkipHeader): static
    {
        $this->shouldSkipHeader = $shouldSkipHeader;

        return $this;
    }

    public function massCreate($shouldMassCreate = true): static
    {
        $this->shouldMassCreate = $shouldMassCreate;

        return $this;
    }

    public function getSpreadsheetData(): Collection
    {
        return $this->toCollection(new UploadedFile(Storage::disk($this->disk)->path($this->spreadsheet), $this->spreadsheet))
            ->first()
            ->skip((int) $this->shouldSkipHeader);
    }

    public function validated($data, $rules, $customMessages, $line)
    {
        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            Notification::make()
                ->danger()
                ->title('Import Failed')
                ->body(trans('filament-import::validators.message', ['line' => $line, 'error' => $validator->errors()->first()]))
                ->persistent()
                ->send();

            return false;
        }

        return $data;
    }

    public function execute()
    {
        DB::transaction(function () {
            foreach ($this->getSpreadsheetData() as $line => $row) {
                $prepareInsert = collect([]);
                $rules = [];
                $validationMessages = [];

                foreach (Arr::dot($this->fields) as $key => $value) {
                    $field = $this->formSchemas[$key];
                    $fieldValue = $value;

                    if ($field instanceof ImportField) {
                        $fieldValue = $field->doMutateBeforeCreate($row[$value], $row) ?? $row[$value];
                        $rules[$key] = $field->getValidationRules();
                    }

                    $prepareInsert[$key] = $fieldValue;
                }

                $prepareInsert = $this->validated(data: Arr::undot($prepareInsert), rules: $rules, customMessages: $validationMessages, line: $line + 1);

                if (! $prepareInsert) {
                    DB::rollBack();
                    break;
                }

                $prepareInsert = $this->doMutateBeforeCreate($prepareInsert);

                if (! $this->shouldMassCreate) {
                    (new $this->model)
                        ->fill($prepareInsert)
                        ->save();

                    continue;
                }

                $this->model::create($prepareInsert);
            }
        });
    }
}
