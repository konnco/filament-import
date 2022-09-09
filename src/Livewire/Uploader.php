<?php

namespace Konnco\FilamentImport\Livewire;

use Closure;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\Concerns\ListensToEvents;
use Illuminate\Contracts\View\View;
use Livewire\TemporaryUploadedFile;

class Uploader extends Component implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public $file;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('file')
                ->label("")
                ->required()
                ->acceptedFileTypes([
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv'
                ])
                ->imagePreviewHeight('250')
                ->reactive()
                ->afterStateUpdated(function (Closure $get, TemporaryUploadedFile $state) {
                    $this->dispatchFormEvent('filament-import::file-uploaded', $state->getRealPath());
                })
        ];
    }

    public function render()
    {
        return view('filament-import::uploader');
    }
}
