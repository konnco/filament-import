<?php

namespace Konnco\FilamentImport;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Konnco\FilamentImport\Actions\ImportField;
use Maatwebsite\Excel\Concerns\Importable;

class Import
{
    use Importable;

    protected string $spreadsheet;

    protected Collection $fields;

    protected array $formSchemas;

    protected string $model;

    protected $disk = 'local';

    protected $skipHeader = false;

    protected $massCreate = true;

    public static function make(string $spreadsheetFilePath): static
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

    public function skipHeader(bool $skip): static
    {
        $this->skipHeader = $skip;

        return $this;
    }

    public function massCreate($massCreate = true): static
    {
        $this->massCreate = $massCreate;

        return $this;
    }

    public function getSpreadsheetExtension()
    {
        return pathinfo($this->spreadsheet, PATHINFO_EXTENSION);
    }

    public function getSpreadsheetData()
    {
        return $this->toCollection(new UploadedFile(Storage::disk($this->disk)->path($this->spreadsheet), $this->spreadsheet))
                                ->first()
                                ->skip((int) $this->skipHeader);
    }

    public function validated($data, $rules, $customMessages, $line) {
        $validator = Validator::make($data, $rules, $customMessages);

        if($validator->fails()){
            Notification::make()
                ->danger()
                ->title("Import Failed")
                ->body(trans('filament-import::validators.message', ['line'=>$line, 'error' => $validator->errors()->first()]))
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
                        $fieldValue = $field?->doMutateBeforeCreate($row[$value], $row) ?? $row[$value];
                        $rules[$key] = $field->getValidationRules();
                    }

                    $prepareInsert[$key] = $fieldValue;
                }

                $prepareInsert = $this->validated(rules:$rules, data:Arr::undot($prepareInsert), line:$line+1, customMessages:$validationMessages);

                if(!$prepareInsert){
                    DB::rollBack();
                    break;
                }

                if (! $this->massCreate) {
                    $this->model::fill($prepareInsert)->save();

                    return;
                }

                $this->model::create($prepareInsert);
            }
        });
    }
}
