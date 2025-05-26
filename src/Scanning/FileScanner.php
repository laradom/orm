<?php

declare(strict_types=1);

namespace Laradom\ORM\Scanning;

use Illuminate\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
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
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * @return FileInfo[]
     */
    public function scan(): array
    {
        $entities = [];
        foreach ($this->getFiles() as $file) {
            try {
                $className = $this->getClassNameFromFile($file);

                if (empty($className)) {
                    continue;
                }

                if (!class_exists($className)) {
                    continue;
                }

                $entities[] = new FileInfo(
                    $file->getFilename(),
                    $file->getPathname(),
                    $className,
                );
            } catch (Throwable $e) {
                $this->logger?->error("[Laradom] Ошибка при сканировании файла {$file->getPathname()}: {$e->getMessage()}");
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
            if (empty($path)) {
                continue;
            }

            if (!$this->files->isDirectory($path)) {
                continue;
            }

            try {
                $directoryFiles = $this->files->allFiles($path);
                $phpFiles = array_filter($directoryFiles, fn (SplFileInfo $file) => $file->getExtension() === 'php');
                $files = array_merge($files, array_values($phpFiles));
            } catch (Throwable $e) {
                $this->logger?->error("[Laradom] Ошибка при сканировании директории {$path}: {$e->getMessage()}");
            }
        }

        $uniqueFiles = [];
        $paths = [];

        foreach ($files as $file) {
            $path = $file->getPathname();

            if (!in_array($path, $paths, true)) {
                $paths[] = $path;
                $uniqueFiles[] = $file;
            }
        }

        return $uniqueFiles;
    }

    private function getClassNameFromFile(SplFileInfo $file): ?string
    {
        try {
            $content = $file->getContents();
            $tokens = token_get_all($content);
            $namespaceFound = false;
            $classFound = false;
            $namespace = '';
            $className = '';

            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] === T_NAMESPACE) {
                        $namespaceFound = true;
                        continue;
                    }

                    if ($namespaceFound && ($token[0] === T_STRING || $token[0] === T_NAME_QUALIFIED)) {
                        $namespace .= $token[1];
                    }

                    if (!$classFound && $token[0] === T_CLASS) {
                        $classFound = true;
                        continue;
                    }

                    if ($classFound && $token[0] === T_STRING) {
                        $className = $token[1];
                        break;
                    }
                } else {
                    if ($namespaceFound && $token === ';') {
                        $namespaceFound = false;
                    }
                }
            }

            if ($className !== '') {
                return $namespace ? $namespace . '\\' . $className : $className;
            }
        } catch (Throwable $e) {
            $this->logger?->error("[Laradom] Ошибка при парсинге файла {$file->getPathname()}: {$e->getMessage()}");
        }

        return null;
    }
}
