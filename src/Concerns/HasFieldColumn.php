<?php

namespace Konnco\FilamentImport\Concerns;

use Illuminate\Support\Str;

trait HasFieldColumn
{
    /**
     * @var string
     */
    protected ?string $column = null;

    /**
     * @return $this
     */
    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function getColumn(): string
    {
        return $this->column ?? $this->name;
    }
}
