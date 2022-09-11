<?php

namespace Konnco\FilamentImport\Concerns;

use Closure;

trait HasFieldMutation
{
    protected bool|Closure $mutateBeforeCreate = false;

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
}
