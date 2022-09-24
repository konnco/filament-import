<?php

it('can render import properly', function () {
    livewire()->assertSuccessful();
});

it('can upload file', function () {
    $file = csvFiles(10);

    livewire()->callPageAction('import', [
        'fileRealPath' => $file->getRealPath(),
        'file' => [\Illuminate\Support\Facades\Storage::path($file->store('file'))],
        'title' => 0,
        'slug' => 1,
        'body' => 2
    ])->assertSuccessful();

    \Pest\Laravel\assertDatabaseCount(\Konnco\FilamentImport\Tests\Resources\Models\Post::class, 10);
});

//it('can import csv, xlsx files', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can publishing config', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can make field required', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can disable mass create', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can manipulate single field', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can manipulate mass field', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can make grid column', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can render filament field', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can save json casting field', function () {
//    expect(true)->toBeTrue();
//});
//
//it('can validating field', function () {
//    expect(true)->toBeTrue();
//});
