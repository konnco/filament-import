# Filament Import

<p align="center">
    <a href="https://filamentadmin.com/docs/2.x/admin/installation">
        <img alt="FILAMENT 1.x" src="https://img.shields.io/badge/FILAMENT-1.x-EBB304">
    </a>
    <a href="https://packagist.org/packages/konnco/filament-import">
        <img alt="Packagist" src="https://img.shields.io/packagist/v/konnco/filament-import.svg?logo=packagist">
    </a>
    [![Fix PHP code style issues](https://github.com/konnco/filament-import/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/konnco/filament-import/actions/workflows/php-cs-fixer.yml)
    <a href="https://packagist.org/packages/konnco/filament-import">
    <img alt="Downloads" src="https://img.shields.io/packagist/dt/konnco/filament-import.svg" >
    </a>
</p>

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
