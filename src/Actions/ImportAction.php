<?php

namespace Konnco\FilamentImport\Actions;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions\Action;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Konnco\FilamentImport\Concerns\HasActionMutation;
use Konnco\FilamentImport\Concerns\HasActionUniqueField;
use Konnco\FilamentImport\Concerns\HasTemporaryDisk;
use Konnco\FilamentImport\Import;
use Livewire\TemporaryUploadedFile;
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
                                    ->except('fileRealPath', 'file', 'skipHeader');

                Import::make(spreadsheetFilePath: $data['file'])
                    ->fields($selectedField)
                    ->formSchemas($this->fields)
                    ->uniqueField($this->uniqueField)
                    ->model($model)
                    ->disk('local')
                    ->skipHeader((bool) $data['skipHeader'])
                    ->massCreate($this->shouldMassCreate)
                    ->mutateBeforeCreate($this->mutateBeforeCreate)
                    ->mutateAfterCreate($this->mutateAfterCreate)
                    ->handleRecordCreation($this->handleRecordCreation)
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
        ]);
    }

    public function massCreate($shouldMassCreate = true): static
    {
        $this->shouldMassCreate = $shouldMassCreate;

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

        $fields = $fields->map(fn (ImportField|Field $field) => $this->getFields($field))->toArray();

        $this->form(
            array_merge(
                $this->getFormSchema(),
                [
                    Fieldset::make(__('filament-import::actions.match_to_column'))
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
     * @param  ImportField|Field  $field
     * @return Field
     */
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

                $selected = array_search($field->getName(), $options);
                if ($selected != false) {
                    $set($field->getName(), $selected);
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
