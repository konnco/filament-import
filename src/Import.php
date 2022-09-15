<?php
namespace Konnco\FilamentImport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Import {
    use Importable;

    protected $spreadsheet;

    protected $fields = [];

    protected $model;

    protected $disk = "local";

    protected $skipHeader = false;

    protected $massCreate = true;

    public static function make(Collection $spreadsheetFilePath):static{
        return (new self)
            ->spreadsheet($spreadsheetFilePath);
    }

    public function fields(Collection $fields):static{
        $this->fields = $fields;
        return $this;
    }

    public function spreadsheet($spreadsheet):static{
        $this->spreadsheet = $spreadsheet;
        return $this;
    }

    public function model(Model $model):static{
        $this->model = $model;
        return $this;
    }

    public function disk($disk = "local"):static {
        $this->disk = $disk;
        return $this;
    }

    public function skipHeader(bool $skip):static{
        $this->skipHeader = $skip;
        return $this;
    }

    public function massCreate($massCreate = true):static{
        $this->massCreate = $massCreate;
        return $this;
    }

    public function getSpreadsheetExtension(){
        return pathinfo($this->spreadsheet, PATHINFO_EXTENSION);
    }

    public function getSpreadsheetData(){
        return $this->toCollection(new UploadedFile(Storage::disk($this->disk)->path($this->spreadsheet), $this->spreadsheet))
                                ->first()
                                ->skip((int) $this->skipHeader);
    }

    public function execute(){
        DB::transaction(function () {
            $this->getSpreadsheetData()->each(function ($row) {
                $prepareInsert = [];

                if($this->getSpreadsheetExtension() == "xls"){
                    $this->fields->each(function ($value, $key) use (&$prepareInsert) {
                        $prepareInsert[$key] = $this->fields[$key]?->doMutateBeforeCreate($value);
                    });
                }

                if($this->getSpreadsheetExtension() == "csv"){
                    $this->fields->each(function ($key, $value) use (&$prepareInsert) {
                        $prepareInsert[$key] = $this->fields[$key]?->doMutateBeforeCreate($value);
                    });
                }

                if($this->massCreate){
                    $this->model::create($prepareInsert);
                    return;
                }

                $model = new $this->model;
                $model->fill($prepareInsert);
                $model->save();
            });
        });
    }
}
