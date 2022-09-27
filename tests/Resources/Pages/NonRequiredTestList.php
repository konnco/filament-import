<?php

namespace Konnco\FilamentImport\Tests\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Tests\Resources\PostResource;

class NonRequiredTestList extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make('import')
                ->fields([
                    ImportField::make('title'),
                    ImportField::make('slug')
                        ->rules('min:6')
                        ->required(),
                    ImportField::make('body')
                        ->required(),
                ])
                ->mutateBeforeCreate(function ($data) {
                    $data['title'] = '';

                    return $data;
                }),
        ];
    }
}
