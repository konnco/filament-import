<?php

namespace Konnco\FilamentImport\Concerns;

use Closure;
use Illuminate\Support\Collection;

trait HasFieldMutation
{
    protected bool|Closure $mutateBeforeCreate = false;

    public function mutateBeforeCreate(bool|Closure $fn): static
    {
        $this->mutateBeforeCreate = $fn;

        return $this;
    }

    public function doMutateBeforeCreate(mixed $state, Collection $row)
    {
        $closure = $this->mutateBeforeCreate;

        if (! $closure) {
            return $state;
        }

        return $closure($state, $row);
    }
}
