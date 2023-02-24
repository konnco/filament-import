<?php

namespace Konnco\FilamentImport\Concerns;

trait HasTemporaryDisk
{
    protected string $temporaryDisk;

    protected string $temporaryDirectory;

    /**
     * @return mixed
     */
    public function getTemporaryDisk()
    {
        return $this->temporaryDisk ?? config('filament-import.temporary_files.disk');
    }

    public function temporaryDisk(string $temporaryDisk): void
    {
        $this->temporaryDisk = $temporaryDisk;
    }

    public function getTemporaryDirectory(): mixed
    {
        return $this->temporaryDirectory ?? config('filament-import.temporary_files.directory');
    }

    public function temporaryDirectory(string $temporaryPath): void
    {
        $this->temporaryDirectory = $temporaryPath;
    }
}
