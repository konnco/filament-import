<?php

namespace Konnco\FilamentImport;

use Filament\PluginServiceProvider;
use Konnco\FilamentImport\Livewire\Uploader;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;

class FilamentImportServiceProvider extends PluginServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-import')
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        Livewire::component('filament-import::uploader', Uploader::class);
    }
}
