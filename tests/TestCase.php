<?php

namespace Konnco\FilamentImport\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Konnco\FilamentImport\FilamentImportServiceProvider;
use Konnco\FilamentImport\Tests\Resources\Panels\TestPanelProvider;
use Livewire\LivewireServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            ActionsServiceProvider::class,
            WidgetsServiceProvider::class,
            SupportServiceProvider::class,
            NotificationsServiceProvider::class,
            TablesServiceProvider::class,
            FilamentImportServiceProvider::class,
            ExcelServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $migration = include __DIR__.'/Migrations/post_migration.php';
        $migration->up();

        config()->set('filament.resources.namespace', 'Konnco\\FilamentImport\\Tests\\Resources');
        config()->set('filament.resources.path', __DIR__.'/Resources');

        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }
}
