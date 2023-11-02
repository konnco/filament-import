<?php

namespace Konnco\FilamentImport\Concerns;

trait HasTemporaryDisk
{
    protected string $temporaryDisk;

    protected string $temporaryDirectory;

    public function getTemporaryDisk(): mixed
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

    public function temporaryDirectory(string $temporaryPath): static
    {
        $this->temporaryDirectory = $temporaryPath;

        return $this;
    }

    public function temporaryDiskIsRemote(): bool
    {
        $driver = config("filesystems.disks.{$this->getTemporaryDisk()}.driver");

        return in_array($driver, ['s3', 'ftp', 'sftp']);
    }
}
