<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityMetadataFactoryTest extends TestCase
{
    private DriverInterface|MockObject $driver;
    private CacheRepository|MockObject $cache;
    private MetadataProcessorPipeline|MockObject $metadataProcessor;
    private EntityMetadataFactory $factory;
    private bool $cacheEnabled = true;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->cache = $this->createMock(CacheRepository::class);
        $this->metadataProcessor = $this->createMock(MetadataProcessorPipeline::class);

        $this->factory = new EntityMetadataFactory(
            $this->driver,
            $this->cache,
            $this->metadataProcessor,
            $this->cacheEnabled,
        );
    }

    public function testGetEntityMetadataForNonExistentClassThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('The "NonExistentClass" class was not found. Make sure that the class exists and is available.');

        $this->factory->getEntityMetadata('NonExistentClass');
    }

    public function testGetEntityMetadataFromCache(): void
    {
        $className = self::class;
        $cacheKey = "laradom.metadata:{$className}";
        $metadata = new EntityMetadata($className);

        $this->cache->expects($this->once())
            ->method('has')
            ->with($cacheKey)
            ->willReturn(true);

        $this->cache->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn($metadata);

        $this->driver->expects($this->never())
            ->method('extractMetadata');

        $this->metadataProcessor->expects($this->never())
            ->method('process');

        $result = $this->factory->getEntityMetadata($className);

        $this->assertSame($metadata, $result);
    }

    public function testGetEntityMetadataBuildsMetadataWhenNotInCache(): void
    {
        $className = self::class;
        $cacheKey = "laradom.metadata:{$className}";
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'processed_table');

        $this->cache->expects($this->once())
            ->method('has')
            ->with($cacheKey)
            ->willReturn(false);

        $this->driver->expects($this->once())
            ->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->expects($this->once())
            ->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $this->cache->expects($this->once())
            ->method('rememberForever')
            ->with($cacheKey, $this->callback(function ($callback) use ($processedMetadata) {
                $result = $callback();

                return $result === $processedMetadata;
            }));

        $result = $this->factory->getEntityMetadata($className);

        $this->assertSame($processedMetadata, $result);
    }

    public function testGetEntityMetadataWithDisabledCache(): void
    {
        $className = self::class;
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'processed_table');

        $factory = new EntityMetadataFactory(
            $this->driver,
            $this->cache,
            $this->metadataProcessor,
            false,
        );

        $this->cache->expects($this->never())
            ->method('has');

        $this->driver->expects($this->once())
            ->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->expects($this->once())
            ->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $this->cache->expects($this->never())
            ->method('rememberForever');

        $result = $factory->getEntityMetadata($className);

        $this->assertSame($processedMetadata, $result);
    }

    public function testGetEntityMetadataWhenCacheReturnsInvalidValue(): void
    {
        $className = self::class;
        $cacheKey = "laradom.metadata:{$className}";
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'processed_table');

        $this->cache->expects($this->once())
            ->method('has')
            ->with($cacheKey)
            ->willReturn(true);

        $this->cache->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn('not an EntityMetadata instance');

        $this->driver->expects($this->once())
            ->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->expects($this->once())
            ->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $this->cache->expects($this->once())
            ->method('rememberForever')
            ->with($cacheKey, $this->callback(function ($callback) use ($processedMetadata) {
                $result = $callback();

                return $result === $processedMetadata;
            }));

        $result = $this->factory->getEntityMetadata($className);

        $this->assertSame($processedMetadata, $result);
    }
}
