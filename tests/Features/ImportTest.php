<?php

use Konnco\FilamentImport\Tests\Resources\Models\Post;
use Konnco\FilamentImport\Tests\Resources\Pages\AlternativeColumnsNamesList;
use Konnco\FilamentImport\Tests\Resources\Pages\HandleCreationList;
use Konnco\FilamentImport\Tests\Resources\Pages\NonRequiredTestList;
use Konnco\FilamentImport\Tests\Resources\Pages\ValidateTestList;
use Konnco\FilamentImport\Tests\Resources\Pages\WithoutMassCreateTestList;

use function Pest\Laravel\assertDatabaseCount;

it('can render import properly', function () {
    livewire()->assertSuccessful();
});

it('can upload file', function () {
    $file = csvFiles(10);
    livewire()->mountAction('import')
        ->setActionData([
            'file' => $file,
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can handling record creation', function () {
    $file = csvFiles(10);
    livewire(HandleCreationList::class)->mountAction('import')
        ->setActionData([
            'file' => $file,
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can upload file and skip header', function () {
    $file = csvFiles(10);
    livewire()->mountAction('import')
        ->setActionData([
            'file' => $file,
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can validate with laravel rules', function () {
    $file = csvFiles(10, ['hello', 'hello', 'hello']);

    livewire(ValidateTestList::class)->mountAction('import')
        ->setActionData([
            'file' => $file,
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 0);
});

it('can disable mass create', function () {
    $file = csvFiles(10);
    livewire(WithoutMassCreateTestList::class)->mountAction('import')
        ->setActionData([
            'file' => $file,
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can ignore non required fields', function () {
    $file = csvFiles(10);
    livewire(NonRequiredTestList::class)->mountAction('import')
        ->setActionData([
            'file' => $file,
            // 'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can match alternative column names', function () {
    $file = csvFiles(10);
    livewire(AlternativeColumnsNamesList::class)->mountAction('import')
        ->setActionData([
            'file' => $file,
            'skipHeader' => false,
        ])
        ->assertActionDataSet(['title' => 0, 'slug' => 1, 'body' => 2])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

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
