<?php

namespace Konnco\FilamentImport\Concerns;

use Closure;

trait HasActionMutation
{
    protected bool|Closure $mutateBeforeCreate = false;

    public function mutateBeforeCreate(Closure $fn): static
    {
        $this->mutateBeforeCreate = $fn;

        return $this;
    }

    public function doMutateBeforeCreate(array $row)
    {
        $closure = $this->mutateBeforeCreate;

        if (! $closure) {
            return $row;
        }

        return $closure($row);
    }
}
