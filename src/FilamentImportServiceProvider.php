<?php

namespace Konnco\FilamentImport;

use Filament\PluginServiceProvider;
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
