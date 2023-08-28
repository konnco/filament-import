<?php

namespace Konnco\FilamentImport\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\App;
use Konnco\FilamentImport\Concerns\HasActionMutation;
use Konnco\FilamentImport\Concerns\HasActionUniqueField;
use Konnco\FilamentImport\Concerns\HasTemporaryDisk;
use Konnco\FilamentImport\Import;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Concerns\Importable;

class ImportAction extends Action
{
    use CanCustomizeProcess;
    use Importable;
    use HasTemporaryDisk;
    use HasActionMutation;
    use HasActionUniqueField;

    protected array $fields = [];

    protected bool $shouldMassCreate = true;

    protected bool $shouldHandleBlankRows = false;

    protected array $cachedHeadingOptions = [];

    protected ?Closure $handleRecordCreation = null;

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
                    ->except('fileRealPath', 'file', 'skipHeader');

                Import::make(spreadsheetFilePath: $data['file'])
                    ->fields($selectedField)
                    ->formSchemas($this->fields)
                    ->uniqueField($this->uniqueField)
                    ->model($model)
                    ->disk($this->getTemporaryDisk())
                    ->skipHeader((bool) $data['skipHeader'])
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
        $this->form($this->getInitialFormSchema());
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
                $this->getInitialFormSchema(),
                [
                    Fieldset::make(__('filament-import::actions.match_to_column'))
                        ->schema($fields)
                        ->columns($columns)
                        ->visible(function (callable $get) {
                            return filled($get('file'));
                        }),
                ]
            )
        );

        return $this;
    }

    protected function getInitialFormSchema(): array
    {
        return [
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
            Toggle::make('skipHeader')
                ->default(true)
                ->label(__('filament-import::actions.skip_header')),
        ];
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

                if (count($options) === 0) {
                    $options = $this->toCollection($filePath, ! App::runningUnitTests() ? $this->getTemporaryDisk() : null)->first()?->first()->filter(fn ($value) => $value != null)->map('trim')->toArray();
                }

                $selected = array_search($field->getName(), $options);

                if ($selected !== false) {
                    $set($field->getName(), $selected);
                } elseif (! empty($field->getAlternativeColumnNames())) {
                    $alternativeNames = array_intersect($field->getAlternativeColumnNames(), $options);
                    if (count($alternativeNames) > 0) {
                        $set($field->getName(), array_search(current($alternativeNames), $options));
                    }
                }

                return $options;
            });
    }

    public function handleRecordCreation(Closure $closure): static
    {
        $this->handleRecordCreation = $closure;
        $this->massCreate(false);

        return $this;
    }
}
