<?php

use Konnco\FilamentImport\Tests\Resources\Models\Post;
use Konnco\FilamentImport\Tests\Resources\Pages\AlternativeColumnsNamesList;
use Konnco\FilamentImport\Tests\Resources\Pages\HandleCreationList;
use Konnco\FilamentImport\Tests\Resources\Pages\HandleUpdateList;
use Konnco\FilamentImport\Tests\Resources\Pages\NonRequiredTestList;
use Konnco\FilamentImport\Tests\Resources\Pages\ValidateTestList;
use Konnco\FilamentImport\Tests\Resources\Pages\WithoutMassCreateTestList;
use function Pest\Laravel\assertDatabaseCount;

it('can render import properly', function () {
    livewire()->assertSuccessful();
});

it('can upload file', function () {
    $file = csvFiles(10);
    livewire()->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can handling record creation', function () {
    $file = csvFiles(10);
    livewire(HandleCreationList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can upload file and skip header', function () {
    $file = csvFiles(10);
    livewire()->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can validate with laravel rules', function () {
    $file = csvFiles(10, ['hello', 'hello', 'hello']);

    livewire(ValidateTestList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 0);
});

it('can disable mass create', function () {
    $file = csvFiles(10);
    livewire(WithoutMassCreateTestList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can ignore non required fields', function () {
    $file = csvFiles(10);
    livewire(NonRequiredTestList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            // 'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can match alternative column names', function () {
    $file = csvFiles(10);
    livewire(AlternativeColumnsNamesList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'skipHeader' => false,
        ])
        ->assertPageActionDataSet(['title' => 0, 'slug' => 1, 'body' => 2])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can handling record update', function (bool $upsert, int $totalRecords) {
    
    $file = csvFiles(0, ['New Title', 'Slug', 'New Body']);
    
    config()->set('filament-import.upsert.active', $upsert);
    
    if($upsert) {
        $live = livewire(HandleUpdateList::class);
    } else {
        $live = livewire(HandleCreationList::class);
    }
    
    $live->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, $totalRecords);

})->with([
    ['upsert' => false, 'totalRecords' => 2],
    ['upsert' => true, 'totalRecords' => 1],
]);

it('can handling update only form fields', function (bool $only) {
    
    config()->set('filament-import.upsert.active', true);
    config()->set('filament-import.upsert.only_form_fields', $only);
    
    if($only === true) {
        $file = csvFiles(0);
    } else {
        $file = csvFiles(0, ['Title', 'Slug', 'New Body']);
    }
    
    livewire(HandleUpdateList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 1);

    if($only === true) {
        $this->assertSame('Body', Post::first()->body);
    } else {
        $this->assertSame('New Body', Post::first()->body);
    }
    
})->with([
    ['only' => false],
    ['only' => true]
]);

//it('can manipulate single field', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can manipulate mass field', function () {
//    expect(true)->toBeTrue();
//});

//it('can save json casting field', function () {
//    expect(true)->toBeTrue();
//});
