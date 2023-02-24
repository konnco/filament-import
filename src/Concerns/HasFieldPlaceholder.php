<?php

namespace Konnco\FilamentImport\Concerns;

trait HasFieldPlaceholder
{
    protected ?string $placeholder = null;

    /**
     * @return $this
     */
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
