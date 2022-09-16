# Filament Import


<a href="https://filamentadmin.com/docs/2.x/admin/installation">
    <img alt="FILAMENT 2.x" src="https://img.shields.io/badge/FILAMENT-2.x-EBB304">
</a>
<a href="https://packagist.org/packages/konnco/filament-import">
    <img alt="Packagist" src="https://img.shields.io/packagist/v/konnco/filament-import.svg?logo=packagist">
</a>
<a href="https://packagist.org/packages/konnco/filament-import">
    <img alt="Downloads" src="https://img.shields.io/packagist/dt/konnco/filament-import.svg" >
</a>

[![Code Styles](https://github.com/konnco/filament-import/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/konnco/filament-import/actions/workflows/php-cs-fixer.yml)
[![run-tests](https://github.com/konnco/filament-import/actions/workflows/run-tests.yml/badge.svg)](https://github.com/konnco/filament-import/actions/workflows/run-tests.yml)

make it easy to import spreadsheets to databases with dynamic mapping forms

## Screenshots

![Screenshot of Login](./art/screenshot.png)

## Installation

You can install the package via composer:

```bash
composer require konnco/filament-import
```

## Usage

import the actions into `ListRecords` page

```php
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\ImportField;

class ListCredentialDatabases extends ListRecords
{
    protected static string $resource = CredentialDatabaseResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->fields([
                    ImportField::make('project')
                        ->label('Project')
                        ->helperText('Define as project helper'),
                    ImportField::make('manager')
                        ->label('Manager'),
                ])
        ];
    }
}
```
### Required Field
```php
protected function getActions(): array
{
    return [
        ImportAction::make()
            ->fields([
                ImportField::make('project')
                    ->label('Project')
                    ->required(),
            ])
    ];
}
```

### Disable Mass Create
if you still want to stick with the event model you might need this and turn off mass create
```php
protected function getActions(): array
{
    return [
        ImportAction::make()
            ->massCreate(false)
            ->fields([
                ImportField::make('project')
                    ->label('Project')
                    ->required(),
            ])
    ];
}
```

### Mutate Data
you can also manipulate data from row spreadsheet before saving to model
```php
protected function getActions(): array
{
    return [
        ImportAction::make()
            ->fields([
                ImportField::make('project')
                    ->label('Project')
                    ->mutateBeforeCreate(fn($string) => Str::of($string)->camelCase())
                    ->required(),
            ])
    ];
}
```

### Grid Column
Of course, you can divide the column grid into several parts to beautify the appearance of the data map
```php
protected function getActions(): array
{
    return [
        ImportAction::make()
            ->fields([
                ImportField::make('project')
                    ->label('Project')
                    ->required(),
            ], columns:2)
    ];
}
```


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/konnco/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Collaborators

<!-- readme: collaborators -start -->
<table>
<tr>
    <td align="center">
        <a href="https://github.com/abduromanov">
            <img src="https://avatars.githubusercontent.com/u/37548312?v=4" width="100;" alt="abduromanov"/>
            <br />
            <sub><b>Hafiz Abd</b></sub>
        </a>
    </td></tr>
</table>
<!-- readme: collaborators -end -->

## Contributors

<!-- readme: contributors -start -->
<table>
<tr>
    <td align="center">
        <a href="https://github.com/frankyso">
            <img src="https://avatars.githubusercontent.com/u/5705520?v=4" width="100;" alt="frankyso"/>
            <br />
            <sub><b>Franky So</b></sub>
        </a>
    </td>
    <td align="center">
        <a href="https://github.com/rizkyanfasafm">
            <img src="https://avatars.githubusercontent.com/u/24226461?v=4" width="100;" alt="rizkyanfasafm"/>
            <br />
            <sub><b>Rizky Anfasa Farras Mada</b></sub>
        </a>
    </td>
    <td align="center">
        <a href="https://github.com/tryoasnafi">
            <img src="https://avatars.githubusercontent.com/u/61939827?v=4" width="100;" alt="tryoasnafi"/>
            <br />
            <sub><b>Tryo Asnafi</b></sub>
        </a>
    </td></tr>
</table>
<!-- readme: contributors -end -->
