<?php

namespace Konnco\FilamentImport\Concerns;

trait HasAdditionalMatches
{
    /**
     * @var array
     */
    protected ?array $matches = [];

    /**
     * @return $this
     */
    public function additionalMatches(array $matches): static
    {
        $this->matches = $matches;

        return $this;
    }

    public function getAdditionalMatches(): array
    {
        return $this->matches;
    }
}
