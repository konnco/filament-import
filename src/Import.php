<?php

namespace Konnco\FilamentImport;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $spreadsheet = $this->toCollection(new UploadedFile(Storage::disk($this->disk)->path($this->spreadsheet), $this->spreadsheet))->first();

        if ($this->skipHeader) {
            $header = $spreadsheet[(int) $this->skipHeader - 1];

            return $spreadsheet->skip((int) $this->skipHeader)->map(fn ($row) => $row->mapWithKeys(fn ($value, $key) => [$header[$key] => $value]));
        }

        return $spreadsheet->skip((int) $this->skipHeader);
    }

    public function execute()
    {
        DB::transaction(function () {
            foreach ($this->getSpreadsheetData() as $row) {
                $prepareInsert = collect([]);

                foreach (Arr::dot($this->fields) as $key => $value) {
                    $field = $this->formSchemas[$key];
                    $fieldValue = $value;

                    if ($field instanceof ImportField) {
                        if ($this->skipHeader) {
                            $header = $row->keys();
                            $fieldValue = $field?->doMutateBeforeCreate($row[$header[$value]], $row) ?? $row[$value];
                        } else {
                            $fieldValue = $field?->doMutateBeforeCreate($row[$value], $row) ?? $row[$value];
                        }
                    }

                    $prepareInsert[$key] = $fieldValue;
                }

                $prepareInsert = Arr::undot($prepareInsert);

                if (! $this->massCreate) {
                    $this->model::fill($prepareInsert)->save();

                    return;
                }

                $this->model::create($prepareInsert);
            }
        });
    }
}
