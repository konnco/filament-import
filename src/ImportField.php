<?php

namespace Konnco\FilamentImport;

use Konnco\FilamentImport\Actions\ImportField as ActionsImportField;

/**
 * @deprecated
 */
class ImportField extends ActionsImportField
{
    public static function make(string $name): static
    {
        return new self($name);
    }
}
