<?php

namespace Konnco\FilamentImport\Actions;

use Closure;
use Filament\Forms;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions\Action;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Konnco\FilamentImport\ImportField;
use Livewire\TemporaryUploadedFile;
use Maatwebsite\Excel\Concerns\Importable;

class ImportAction extends Action
{
    use CanCustomizeProcess;
    use Importable;

    protected bool | Closure $isCreateAnotherDisabled = false;

    protected ?Closure $mutateBeforeCreate;

    protected $fields = [];

    protected $cachedOptions;

    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('filament-import::actions.import'));

        $this->form([
            Forms\Components\FileUpload::make('file')
                ->label('')
                ->required()
                ->acceptedFileTypes([
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv',
                ])
                ->imagePreviewHeight('250')
                ->reactive()
                ->disk('local')
                ->directory('filament-import')
                ->afterStateUpdated(function (callable $set, TemporaryUploadedFile $state) {
                    $set('path', $state->getRealPath());
                }),
            Toggle::make('skipHeader')
                ->default(true)
                ->label('Skip header'),
        ]);

        $this->button();

        $this->groupedIcon('heroicon-s-plus');

        $this->action(function (ComponentContainer $form): void {
            $model = $form->getModel();
            $this->process(function (array $data) use ($model) {
                $selectedField = collect($data)->except('path', 'file', 'skipHeader');

                $spreadsheet = $this->toCollection(new UploadedFile(Storage::disk('local')->path($data['file']), $data['file']))
                                ->first()
                                ->skip((int) $data['skipHeader']);

                DB::transaction(function () use ($spreadsheet, $selectedField, $model) {
                    $spreadsheet->each(function ($row) use ($selectedField, $model) {
                        $prepareInsert = [];

                        $selectedField->each(function ($value, $key) use (&$prepareInsert) {
                            $prepareInsert[$key] = $this->fields[$key]?->doMutateBeforeCreate($value);
                        });

                        $model::create($prepareInsert);
                    });
                });
            });
        });
    }

    public function getExcelReaderType($path)
    {
        $infoPath = pathinfo($path);

        $extension = Str::of($infoPath['extension'])->ucfirst();

        return $extension;
    }

    public function mutateBeforeCreate(?Closure $callback): static
    {
        $this->mutateBeforeCreate = $callback;

        return $this;
    }

    public function fields(array $fields, $columns = 1): static
    {
        $this->fields = collect($fields)->mapWithKeys(fn ($item) => [$item->getName() => $item])->toArray();

        $fields = collect($fields);

        $fields = $fields->map(fn (ImportField $field) => $this->mapField($field))->toArray();
        $fields[] = Hidden::make('path');

        $this->form(
            array_merge(
                $this->getFormSchema(),
                [
                    Fieldset::make('Data matching')
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

    public function columns(int $columns)
    {
        $this->columns = $columns;
    }

    private function mapField(ImportField $field)
    {
        return Select::make($field->getName())
                ->helperText($field->getHelperText())
                ->required($field->isRequired())
                ->placeholder($field->getPlaceholder())
                ->options(function (callable $get) {
                    /**
                     * @var TemporaryUploadedFile $uploadedFile
                     */
                    $uploadedFile = last($get('file') ?? []);
                    $filePath = $uploadedFile->getRealPath();

                    return $this->cachedOptions ?? $this->cachedOptions = $this->toCollection($filePath)?->first()?->first();
                });
    }
}
