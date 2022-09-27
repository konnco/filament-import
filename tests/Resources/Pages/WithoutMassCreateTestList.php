<?php

namespace Konnco\FilamentImport\Tests\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Tests\Resources\PostResource;

class WithoutMassCreateTestList extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make('import')
                ->massCreate(false)
                ->fields([
                    ImportField::make('title'),
                    ImportField::make('slug')
                        ->rules('min:6'),
                    ImportField::make('body'),
                ]), ];
    }
}
