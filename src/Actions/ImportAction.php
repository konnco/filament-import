<?php

namespace Konnco\FilamentImport\Actions;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Konnco\FilamentImport\Concerns\HasTemporaryDisk;
use Konnco\FilamentImport\Concerns\HasFieldMutation;
use Konnco\FilamentImport\ImportField;
use Livewire\TemporaryUploadedFile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Importer;
use Illuminate\Support\Str;

class ImportAction extends Action
{
    use CanCustomizeProcess;
    use Importable;
    use HasTemporaryDisk;

    protected array $fields = [];
    protected array $cachedHeadingOptions = [];

    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('filament-import::actions.import'));

        $this->setInitialForm();

        $this->button();

        $this->groupedIcon('heroicon-s-plus');

        $this->action(function (ComponentContainer $form): void {
            $model = $form->getModel();
                $this->process(function (array $data) use ($model) {
                    $selectedField = collect($data)->except('fileRealPath','file','skipHeader');

                    $spreadsheet = $this->toCollection(new UploadedFile(Storage::disk('local')->path($data['file']), $data['file']))
                                    ->first()
                                    ->skip((int) $data['skipHeader']);

                    DB::transaction(function () use($spreadsheet, $selectedField, $model) {
                        $spreadsheet->each(function($row) use ($selectedField, $model) {
                            $prepareInsert = [];

                            $selectedField->each(function($value, $key) use (&$prepareInsert) {
                                $prepareInsert[$key] = $this->fields[$key]?->doMutateBeforeCreate($value);
                            });

                            $model::create($prepareInsert);
                        });
                    });
                });
        });
    }

    /**
     * @return void
     */
    public function setInitialForm(): void
    {
        $this->form([
            FileUpload::make('file')
                ->label("")
                ->required()
                ->acceptedFileTypes(config('filament-import.accepted_mimes'))
                ->imagePreviewHeight('250')
                ->reactive()
                ->disk($this->getTemporaryDisk())
                ->directory($this->getTemporaryDirectory())
                ->afterStateUpdated(function (callable $set, TemporaryUploadedFile $state) {
                    $set('fileRealPath', $state->getRealPath());
                }),
            Hidden::make('fileRealPath'),
            Toggle::make('skipHeader')
                ->default(true)
                ->label(__('filament-import::actions.skip_header'))
        ]);
    }

    /**
     * @param array $fields
     * @param int $columns
     * @return $this
     */
    public function fields(array $fields, int $columns = 1):static {
        $this->fields = collect($fields)->mapWithKeys(fn($item)=> [$item->getName() => $item])->toArray();

        $fields = collect($fields);

        $fields = $fields->map(fn(ImportField $field)=>$this->getFields($field))->toArray();

        $this->form(
            array_merge(
                $this->getFormSchema(),
                [
                    Fieldset::make('Data matching')
                        ->schema($fields)
                        ->columns($columns)
                        ->visible(function(Callable $get){
                            return $get('file') != null;
                        })
                ]
            )
        );

        return $this;
    }

    /**
     * @param ImportField $field
     * @return mixed
     */
    private function getFields(ImportField $field): mixed
    {
        return Select::make($field->getName())
                ->helperText($field->getHelperText())
                ->required($field->isRequired())
                ->placeholder($field->getPlaceholder())
                ->options(function(Callable $get){

                    /**
                     * @var TemporaryUploadedFile $uploadedFile
                     */
                    $uploadedFile = last($get('file') ?? []);
                    $filePath = $uploadedFile->getRealPath();

                    return $this->cachedHeadingOptions ?? $this->cachedHeadingOptions = $this->toCollection($filePath)?->first()?->first();
                });
    }
}
