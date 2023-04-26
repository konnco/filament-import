<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Konnco\FilamentImport\Tests\Resources\Pages\CommonTestList;
use Konnco\FilamentImport\Tests\TestCase;
use Livewire\Livewire;

uses(TestCase::class)->in(__DIR__);

function livewire($list = null)
{
    return Livewire::test($list ?? CommonTestList::class);
}

function csvFiles($rows = 10, $extraRow = [], $fields='Title,Slug,Body')
{
    Storage::fake('uploads');

    $content = collect($fields);
    for ($i = 0; $i < $rows; $i++) {
        $content = $content->push(implode(',', [
            fake()->title,
            fake()->slug,
            fake()->text(500),
        ]));
    }

    if (count($extraRow) > 0) {
        $content = $content->push(collect($extraRow)->join(','));
    }

    return UploadedFile::fake()
        ->createWithContent(
            name: 'file.csv',
            content: $content->join("\n")
        );
}
