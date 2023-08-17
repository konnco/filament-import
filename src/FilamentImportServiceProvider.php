<?php

namespace Konnco\FilamentImport;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentImportServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-import')
            ->hasConfigFile()
            ->hasTranslations();
    }
}
