<?php

namespace Konnco\FilamentImport\Concerns;

trait HasFieldPlaceholder
{
    protected ?string $placeholder = null;

    /**
     * @param  string  $placeholder
     * @return $this
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }
}
