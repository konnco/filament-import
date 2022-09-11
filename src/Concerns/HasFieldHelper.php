<?php

namespace Konnco\FilamentImport\Concerns;

trait HasFieldHelper
{
    protected ?string $helperText = null;

    /**
     * @param $text
     * @return $this
     */
    public function helperText($text): static
    {
        $this->helperText = $text;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHelperText(): ?string
    {
        return $this->helperText;
    }
}
