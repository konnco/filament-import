<?php

namespace Konnco\FilamentImport\Tests\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Tests\Resources\Models\Post;
use Konnco\FilamentImport\Tests\Resources\PostResource;

class HandleUpdateList extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make('import')
                ->uniqueField('slug')
                ->fields([
                    ImportField::make('title'),
                    ImportField::make('slug'),
                    ImportField::make('body'),
                ])
        ];
    }
}
