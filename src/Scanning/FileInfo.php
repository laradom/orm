<?php

declare(strict_types=1);

namespace Laradom\ORM\Scanning;

class FileInfo
{
    public function __construct(
        private readonly string $fileName,
        private readonly string $pathname,
        private readonly string $className,
    ) {}

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getPathname(): string
    {
        return $this->pathname;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
