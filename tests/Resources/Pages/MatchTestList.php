<?php

namespace Konnco\FilamentImport\Tests\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Tests\Resources\PostResource;

class MatchTestList extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make('import')
                ->fields([
                    ImportField::make('title')
                        ->additionalMatches(['titulo', 'new title']),
                    ImportField::make('slug')
                        ->additionalMatches(['frase']),
                    ImportField::make('body')
                        ->additionalMatches(['cuerpo', 'texto']),
                ]), ];
    }
}
