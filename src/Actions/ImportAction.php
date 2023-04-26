<?php

namespace Konnco\FilamentImport\Actions;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions\Action;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Konnco\FilamentImport\Concerns\CanSkipFooter;
use Konnco\FilamentImport\Concerns\CanSkipHeader;
use Konnco\FilamentImport\Concerns\HasActionMutation;
use Konnco\FilamentImport\Concerns\HasActionUniqueField;
use Konnco\FilamentImport\Concerns\HasTemporaryDisk;
use Konnco\FilamentImport\Import;
use Livewire\TemporaryUploadedFile;
use Maatwebsite\Excel\Concerns\Importable;

class ImportAction extends Action
{
    use CanCustomizeProcess;
    use CanSkipFooter;
    use CanSkipHeader;
    use Importable;
    use HasTemporaryDisk;
    use HasActionMutation;
    use HasActionUniqueField;

    protected array $fields = [];

    protected bool $shouldMassCreate = true;

    protected bool $shouldHandleBlankRows = false;

    protected array $cachedHeadingOptions = [];

    protected null|Closure $handleRecordCreation = null;

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
                $selectedField = collect($data)
                                    ->except('fileRealPath', 'file', 'skipHeader', 'skipFooter', 'skipFooterCount');

                Import::make(spreadsheetFilePath: $data['file'])
                    ->fields($selectedField)
                    ->formSchemas($this->fields)
                    ->uniqueField($this->uniqueField)
                    ->model($model)
                    ->disk('local')
                    ->skipHeader((bool) $data['skipHeader'])
                    ->skipFooterCount($data['skipFooter'] ? $data['skipFooterCount'] : 0)
                    ->massCreate($this->shouldMassCreate)
                    ->handleBlankRows($this->shouldHandleBlankRows)
                    ->mutateBeforeCreate($this->mutateBeforeCreate)
                    ->mutateAfterCreate($this->mutateAfterCreate)
                    ->handleRecordCreation($this->handleRecordCreation)
                    ->execute();
            });
        });
    }

    public function setInitialForm(): void
    {
        $this->form([
            FileUpload::make('file')
                ->label('')
                ->required(! app()->environment('testing'))
                ->acceptedFileTypes(config('filament-import.accepted_mimes'))
                ->imagePreviewHeight('250')
                ->reactive()
                ->disk($this->getTemporaryDisk())
                ->directory($this->getTemporaryDirectory())
                ->afterStateUpdated(function (callable $set, TemporaryUploadedFile $state) {
                    $set('fileRealPath', $state->getRealPath());
                }),
            Hidden::make('fileRealPath'),
        ]);
    }

    public function massCreate($shouldMassCreate = true): static
    {
        $this->shouldMassCreate = $shouldMassCreate;

        return $this;
    }

    public function handleBlankRows($shouldHandleBlankRows = false): static
    {
        $this->shouldHandleBlankRows = $shouldHandleBlankRows;

        return $this;
    }

    /**
     * @return $this
     */
    public function fields(array $fields, int $columns = 1): static
    {
        $this->fields = collect($fields)->mapWithKeys(fn ($item) => [$item->getName() => $item])->toArray();

        $fields = collect($fields);

        $fields = $fields->map(fn (ImportField|Field $field) => $this->getFields($field))->toArray();

        $this->form(
            array_merge(
                $this->getFormSchema(),
                [
                    Grid::make(1)
                        ->schema([
                            Toggle::make('skipHeader')
                                ->default($this->shouldSkipHeader())
                                ->label(__('filament-import::actions.skip_header')),
                            Toggle::make('skipFooter')
                                ->default($this->shouldSkipFooter())
                                ->label(__('filament-import::actions.skip_footer'))
                                ->reactive(),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('skipFooterCount')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default($this->getSkipFooterCount())
                                        ->label(__('filament-import::actions.skip_footer_count'))
                                        ->visible(fn (Closure $get) => $get('skipFooter'))
                                        ->columnSpan(1),
                                ]),
                            Fieldset::make(__('filament-import::actions.match_to_column'))
                                ->schema($fields)
                                ->columns($columns),
                        ])
                        ->visible(function (callable $get) {
                            return $get('file') != null;
                        }),
                ]
            )
        );

        return $this;
    }

    private function getFields(ImportField|Field $field): Field
    {
        if ($field instanceof Field) {
            return $field;
        }

        return Select::make($field->getName())
            ->label($field->getLabel())
            ->helperText($field->getHelperText())
            ->required($field->isRequired())
            ->placeholder($field->getPlaceholder())
            ->options(options: function (callable $get, callable $set) use ($field) {
                $uploadedFile = last($get('file') ?? []);
                $filePath = is_string($uploadedFile) ? $uploadedFile : $uploadedFile?->getRealPath();

                $options = $this->cachedHeadingOptions;

                if (count($options) == 0) {
                    $options = $this->toCollection($filePath)->first()?->first()->filter(fn ($value) => $value != null)->toArray();
                }

                $needles = $field->getAdditionalMatches();
                array_push($needles, $field->getName());
                $matches = array_intersect($needles, $options);

                if (! empty($matches)) {
                    $set($field->getName(), array_search(current($matches), $options));
                }

                return $options;
            });
    }

    public function handleRecordCreation(Closure $closure)
    {
        $this->handleRecordCreation = $closure;
        $this->massCreate(false);

        return $this;
    }
}
