<?php

use Konnco\FilamentImport\Tests\Resources\Models\Post;
use Konnco\FilamentImport\Tests\Resources\Pages\HandleCreationList;
use Konnco\FilamentImport\Tests\Resources\Pages\MatchTestList;
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
            'skipFooter' => false,
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
            'skipFooter' => false,
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
            'skipFooter' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can upload file and skip footer', function () {
    $file = csvFiles(10);
    livewire()->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'title' => 0,
            'slug' => 1,
            'body' => 2,
            'skipHeader' => true,
            'skipFooter' => true,
            'skipFooterCount' => 2,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 8);
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
            'skipFooter' => false,
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
            'skipFooter' => false,
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
            'skipFooter' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 10);
});

it('can match fields to columns', function () {
    $file = csvFiles(10, fields:'title,slug,body');
    livewire(HandleCreationList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can match the first field', function () {
    $file = csvFiles(10, fields:'title,Slug,Body');
    livewire(HandleCreationList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'slug' => 1,
            'body' => 2,
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
        ->assertSuccessful();

    assertDatabaseCount(Post::class, 11);
});

it('can match fields with different matches to columns', function () {
    $file = csvFiles(10, fields:'titulo,frase,cuerpo');
    livewire(MatchTestList::class)->mountPageAction('import')
        ->setPageActionData([
            'file' => [$file->store('file')],
            'fileRealPath' => $file->getRealPath(),
            'skipHeader' => false,
        ])
        ->callMountedPageAction()
        ->assertHasNoPageActionErrors()
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
