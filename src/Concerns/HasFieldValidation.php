<?php

namespace Konnco\FilamentImport\Concerns;

trait HasFieldValidation
{
    protected array|string $rules = [];

    protected $customMessages = [];

    public function rules(array|string $rules = [], $customMessages = []): static
    {
        $this->rules = $rules;
        $this->customMessages = $customMessages;

        return $this;
    }

    public function getValidationRules()
    {
        return $this->rules;
    }

    public function getCustomValidationMessages()
    {
        return $this->customMessages;
    }
}
