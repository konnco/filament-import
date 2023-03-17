<?php

namespace Konnco\FilamentImport\Concerns;

use Illuminate\Support\Str;

trait HasFieldLabel
{
    protected ?string $label = null;

    protected bool $shouldTranslateLabel = false;

    /**
     * @return $this
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function translateLabel(bool $shouldTranslateLabel = true): static
    {
        $this->shouldTranslateLabel = $shouldTranslateLabel;

        return $this;
    }

    public function getLabel(): string
    {
        $label = $this->label ?? (string) Str::of($this->name)
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        return (is_string($label) && $this->shouldTranslateLabel) ?
            __($label) :
            $label;
    }
}
