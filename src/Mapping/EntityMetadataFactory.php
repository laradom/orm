<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Psr\SimpleCache\InvalidArgumentException;

class EntityMetadataFactory
{
    public function __construct(
        private readonly DriverInterface $driver,
        private readonly CacheRepository $cache,
        private readonly MetadataProcessorPipeline $metadataProcessor,
        private readonly bool $cacheEnabled,
    ) {}

    /**
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     */
    public function getEntityMetadata(string $className): EntityMetadata
    {
        if (!class_exists($className)) {
            throw new EntityNotFoundException(
                sprintf('The "%s" class was not found. Make sure that the class exists and is available.', $className),
            );
        }

        $cacheKey = $this->cacheKey($className);

        if ($this->cacheEnabled && $this->cache->has($cacheKey)) {
            $metadata = $this->cache->get($cacheKey);

            if ($metadata instanceof EntityMetadata) {
                return $metadata;
            }
        }

        $metadata = $this->buildMetadata($className);

        if ($this->cacheEnabled) {
            $this->cache->rememberForever(
                $cacheKey,
                static fn () => $metadata,
            );
        }

        return $metadata;
    }

    private function buildMetadata(string $className): EntityMetadata
    {
        $metadata = $this->driver->extractMetadata($className);

        return $this->metadataProcessor->process($metadata);
    }

    private function cacheKey(string $className): string
    {
        return "laradom.metadata:{$className}";
    }
}
