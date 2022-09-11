# Filament Import

[![Latest Version on Packagist](https://img.shields.io/packagist/v/konnco/filament-import.svg?style=flat-square)](https://packagist.org/packages/konnco/filament-import)
[![Total Downloads](https://img.shields.io/packagist/dt/konnco/filament-import.svg?style=flat-square)](https://packagist.org/packages/konnco/filament-import)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/konnco/filament-import/run-tests?label=tests)](https://github.com/konnco/filament-import/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/konnco/filament-import/Check%20&%20fix%20styling?label=code%20style)](https://github.com/konnco/filament-import/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)

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

## Credits

- [Franky So](https://github.com/frankyso)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
