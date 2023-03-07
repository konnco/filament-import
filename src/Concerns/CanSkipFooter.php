<?php

namespace Konnco\FilamentImport\Concerns;

trait CanSkipFooter
{
    protected int $skipFooterCount = 0;

    public function skipFooter(int $skipFooterCount = 1): static
    {
        $this->skipFooterCount = $skipFooterCount;

        return $this;
    }

    public function shouldSkipFooter(): bool
    {
        return $this->skipFooterCount > 0;
    }

    public function getSkipFooterCount(): int
    {
        return $this->skipFooterCount;
    }
}
