<?php

namespace Konnco\FilamentImport\Concerns;

trait HasFieldHelper
{
    protected ?string $helperText = null;

    public function helperText($text): static
    {
        $this->helperText = $text;

        return $this;
    }

    public function getHelperText(): ?string
    {
        return $this->helperText;
    }
}
