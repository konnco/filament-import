<?php
namespace Konnco\FilamentImport;

use Konnco\FilamentImport\Concerns\HasFieldMutation;
use Konnco\FilamentImport\Concerns\HasFieldLabel;
use Konnco\FilamentImport\Concerns\HasFieldHelper;
use Konnco\FilamentImport\Concerns\HasFieldPlaceholder;
use Konnco\FilamentImport\Concerns\HasFieldRequire;

class ImportField
{
    use HasFieldMutation;
    use HasFieldHelper;
    use HasFieldPlaceholder;
    use HasFieldLabel;
    use HasFieldRequire;

    public function __construct(private string $name)
    {

    }

    public static function make(string $name):static{
        return new self($name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
