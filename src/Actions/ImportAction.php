<?php

namespace Konnco\FilamentImport\Actions;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions\Action;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Konnco\FilamentImport\Concerns\HasTemporaryDisk;
use Konnco\FilamentImport\Import;
use Livewire\TemporaryUploadedFile;
use Maatwebsite\Excel\Concerns\Importable;

class ImportAction extends Action
{
    use CanCustomizeProcess;
    use Importable;
    use HasTemporaryDisk;

    protected array $fields = [];

    protected $massCreate = true;

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
                $selectedField = collect($data)->except('fileRealPath', 'file', 'skipHeader');

                Import::make(spreadsheetFilePath:$data['file'])
                    ->fields($selectedField)
                    ->formSchemas($this->fields)
                    ->model($model)
                    ->disk('local')
                    ->skipHeader((bool) $data['skipHeader'])
                    ->massCreate($this->massCreate)
                    ->execute();
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
                ->label('')
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
                ->label(__('filament-import::actions.skip_header')),
        ]);
    }

    public function massCreate($massCreate = true):static
    {
        $this->massCreate = $massCreate;
        return $this;
    }

    /**
     * @param  array  $fields
     * @param  int  $columns
     * @return $this
     */
    public function fields(array $fields, int $columns = 1): static
    {
        $this->fields = collect($fields)->mapWithKeys(fn ($item) => [$item->getName() => $item])->toArray();

        $fields = collect($fields);

        $fields = $fields->map(fn (ImportField $field) => $this->getFields($field))->toArray();

        $this->form(
            array_merge(
                $this->getFormSchema(),
                [
                    Fieldset::make('Match data to column')
                        ->schema($fields)
                        ->columns($columns)
                        ->visible(function (callable $get) {
                            return $get('file') != null;
                        }),
                ]
            )
        );

        return $this;
    }

    /**
     * @param  ImportField  $field
     * @return mixed
     */
    private function getFields(ImportField $field): mixed
    {
        return Select::make($field->getName())
                ->label($field->getLabel())
                ->helperText($field->getHelperText())
                ->required($field->isRequired())
                ->placeholder($field->getPlaceholder())
                ->options(function (callable $get) {
                    /**
                     * @var TemporaryUploadedFile $uploadedFile
                     */
                    $uploadedFile = last($get('file') ?? []);
                    $filePath = $uploadedFile->getRealPath();

                    if (count($this->cachedHeadingOptions) == 0) {
                        return $this->cachedHeadingOptions = $this->toCollection($filePath)?->first()?->first()?->toArray();
                    }

                    return $this->cachedHeadingOptions;
                });
    }
}
