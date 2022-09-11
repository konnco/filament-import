<?php
namespace Konnco\FilamentImport;

use Filament\Events\ServingFilament;
use Filament\PluginServiceProvider;
use Konnco\FilamentImport\Livewire\Uploader;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;

class FilamentImportServiceProvider extends PluginServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-import')
            ->hasConfigFile()
            ->hasTranslations();
    }
}
