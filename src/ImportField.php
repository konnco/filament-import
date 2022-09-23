<?php

namespace Konnco\FilamentImport;

use Konnco\FilamentImport\Actions\ImportField as ActionsImportField;

/**
 * @deprecated moved into ```Konnco\FilamentImport\Actions\ImportField```
 */
class ImportField extends ActionsImportField
{
    public static function make(string $name): self
    {
        return new self($name);
    }
}
