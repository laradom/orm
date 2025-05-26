<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Scanning\FileScanner;
use Psr\SimpleCache\InvalidArgumentException;

class EntityMetadataFactory
{
    public const CACHE_META_DATA = 'laradom:meta_data';
    public const CACHE_META_CHECKSUMS = 'laradom:meta_checksums';
    public const CACHE_META_CLASSES = 'laradom:meta_classes';

    public function __construct(
        private readonly DriverInterface $driver,
        private readonly CacheRepository $cache,
        private readonly MetadataProcessorPipeline $metadataProcessor,
        private readonly FileScanner $fileScanner,
        private readonly bool $cacheEnabled = true,
        private readonly bool $autoInvalidateCache = true,
    ) {}

    /**
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     */
    public function getEntityMetadata(string $className): ?EntityMetadata
    {
        if (!class_exists($className)) {
            throw new EntityNotFoundException(
                sprintf('The "%s" class was not found. Make sure that the class exists and is available.', $className),
            );
        }

        $allMetadata = $this->getAllMetadata();

        if (!isset($allMetadata[$className])) {
            $metadata = $this->buildMetadata($className);

            if ($metadata === null) {
                return null;
            }

            if ($this->cacheEnabled) {
                $allMetadata[$className] = $metadata;
                $this->cache->forever(self::CACHE_META_DATA, $allMetadata);

                $classes = (array) $this->cache->get(self::CACHE_META_CLASSES, []);

                if (!in_array($className, $classes, true)) {
                    $classes[] = $className;
                    $this->cache->forever(self::CACHE_META_CLASSES, $classes);
                }
            }

            return $metadata;
        }

        return $allMetadata[$className];
    }

    /**
     * @return EntityMetadata[]
     *
     * @throws InvalidArgumentException
     */
    public function getAllMetadata(): array
    {
        if (!$this->cacheEnabled) {
            return $this->buildAllMetadata();
        }

        /** @var EntityMetadata[] $metadataMap */
        $metadataMap = $this->cache->get(self::CACHE_META_DATA, []);

        if (count($metadataMap) === 0 || ($this->autoInvalidateCache && $this->shouldInvalidateCache())) {
            $metadataMap = $this->buildAllMetadata();
            $this->cache->forever(self::CACHE_META_DATA, $metadataMap);

            $classes = array_keys($metadataMap);
            $this->cache->forever(self::CACHE_META_CLASSES, $classes);

            $checksums = $this->calculateFileChecksums();
            $this->cache->forever(self::CACHE_META_CHECKSUMS, $checksums);
        }

        return $metadataMap;
    }

    public function clearCache(): void
    {
        $this->cache->forget(self::CACHE_META_DATA);
        $this->cache->forget(self::CACHE_META_CHECKSUMS);
        $this->cache->forget(self::CACHE_META_CLASSES);
    }

    private function buildMetadata(string $className): ?EntityMetadata
    {
        if (!$this->driver->supports($className)) {
            return null;
        }

        $classMetadata = $this->driver->extractMetadata($className);

        return $this->metadataProcessor->process($classMetadata);
    }

    /**
     * @return array<string, EntityMetadata>
     */
    private function buildAllMetadata(): array
    {
        $files = $this->fileScanner->scan();
        $metadataMap = [];

        foreach ($files as $file) {
            $className = $file->getClassName();

            if ($this->driver->supports($className)) {
                $metadata = $this->buildMetadata($className);

                if ($metadata !== null) {
                    $metadataMap[$className] = $metadata;
                }
            }
        }

        return $this->metadataProcessor->postProcess($metadataMap);
    }

    private function shouldInvalidateCache(): bool
    {
        /** @var array<string, string> $savedChecksums */
        $savedChecksums = (array) $this->cache->get(self::CACHE_META_CHECKSUMS, []);

        if (empty($savedChecksums)) {
            return true;
        }

        $currentChecksums = $this->calculateFileChecksums();

        return $this->hasFileChanges($savedChecksums, $currentChecksums);
    }

    /**
     * @return array<string, string>
     */
    private function calculateFileChecksums(): array
    {
        $files = $this->fileScanner->scan();

        $checksums = [];
        foreach ($files as $file) {
            if (!$this->driver->supports($file->getClassName())) {
                continue;
            }

            $path = $file->getPathname();

            if (file_exists($path)) {
                $checksums[$path] = md5_file($path) ?: filemtime($path) . filesize($path);
            } else {
                $checksums[$path] = md5($path);
            }
        }

        return $checksums;
    }

    /**
     * @param array<string, string> $savedChecksums
     * @param array<string, string> $currentChecksums
     */
    private function hasFileChanges(array $savedChecksums, array $currentChecksums): bool
    {
        $newFiles = array_diff_key($currentChecksums, $savedChecksums);

        if (!empty($newFiles)) {
            return true;
        }

        $deletedFiles = array_diff_key($savedChecksums, $currentChecksums);

        if (!empty($deletedFiles)) {
            return true;
        }

        foreach ($currentChecksums as $path => $checksum) {
            if (isset($savedChecksums[$path]) && $savedChecksums[$path] !== $checksum) {
                return true;
            }
        }

        return false;
    }
}
