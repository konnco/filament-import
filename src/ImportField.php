<?php

namespace Konnco\FilamentImport;

use Closure;

class ImportField
{
    protected ?string $helperText = null;

    protected ?string $placeholder = null;

    protected $isRequired = false;

    protected bool|Closure $mutateBeforeCreate = false;

    public function __construct(private string $name)
    {
    }

    public static function make(string $name): static
    {
        return new self($name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function mutateBeforeCreate(Closure $fn): static
    {
        $this->mutateBeforeCreate = $fn;

        return $this;
    }

    public function doMutateBeforeCreate($state)
    {
        $closure = $this->mutateBeforeCreate;

        if (! $closure) {
            return $state;
        }

        return $closure($state);
    }

    public function required(): static
    {
        $this->isRequired = true;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function helperText(): static
    {
        return $this;
    }

    public function getHelperText(): ?string
    {
        return $this->helperText;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }
}
