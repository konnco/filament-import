<?php

namespace Konnco\FilamentImport\Concerns;

trait CanSkipHeader
{
    protected bool $shouldSkipHeader = true;

    public function skipHeader(bool $condition = true): static
    {
        $this->shouldSkipHeader = $condition;

        return $this;
    }

    public function shouldSkipHeader(): bool
    {
        return $this->shouldSkipHeader;
    }
}
