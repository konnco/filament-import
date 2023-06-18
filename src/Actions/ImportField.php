<?php

namespace Konnco\FilamentImport\Actions;

use Konnco\FilamentImport\Concerns\HasColumnMatching;
use Konnco\FilamentImport\Concerns\HasFieldHelper;
use Konnco\FilamentImport\Concerns\HasFieldLabel;
use Konnco\FilamentImport\Concerns\HasFieldMutation;
use Konnco\FilamentImport\Concerns\HasFieldPlaceholder;
use Konnco\FilamentImport\Concerns\HasFieldRequire;
use Konnco\FilamentImport\Concerns\HasFieldValidation;

class ImportField
{
    use HasFieldMutation;
    use HasFieldHelper;
    use HasFieldPlaceholder;
    use HasFieldLabel;
    use HasFieldRequire;
    use HasFieldValidation;
    use HasColumnMatching;

    public function __construct(private string $name)
    {
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
