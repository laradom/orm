<?php

declare(strict_types=1);

namespace Laradom\ORM\Scanning;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class FileScanner
{
    /**
     * @param array<class-string> $paths
     */
    public function __construct(
        private readonly Filesystem $files,
        private readonly array $paths,
    ) {}

    /**
     * @return FileInfo[]
     */
    public function scan(): array
    {
        $entities = [];
        foreach ($this->getFiles() as $file) {
            $className = $this->getClassNameFromFile($file);

            if (!empty($className) && class_exists($className)) {
                $entities[] = new FileInfo(
                    $file->getFilename(),
                    $file->getPathname(),
                    $className,
                );
            }
        }

        return $entities;
    }

    /**
     * @return SplFileInfo[]
     */
    private function getFiles(): array
    {
        $files = [];

        if (empty($this->paths)) {
            return $files;
        }

        foreach ($this->paths as $path) {
            if (empty($path) || !$this->files->isDirectory($path)) {
                continue;
            }

            try {
                $files = $this->files->allFiles($path);
                $files = array_filter($files, fn (SplFileInfo $file) => $file->getExtension() === 'php');
                $files = array_values(array_unique($files));
            } catch (Throwable) {
            }
        }

        return $files;
    }

    private function getClassNameFromFile(SplFileInfo $file): ?string
    {
        try {
            $content = $file->getContents();
            $namespace = null;
            $class = null;

            if (preg_match('/namespace\s+([^\s;]+)\s*;/i', $content, $matches)) {
                $namespace = trim($matches[1]);
            }

            if (preg_match('/class\s+([^\s\{:]+)/i', $content, $matches)) {
                $class = trim($matches[1]);
            }

            if ($namespace && $class) {
                return $namespace . '\\' . $class;
            }
        } catch (Throwable) {
        }

        return null;
    }
}
