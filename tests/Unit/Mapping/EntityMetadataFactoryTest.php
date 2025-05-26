<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Scanning\FileInfo;
use Laradom\ORM\Scanning\FileScanner;
use Laradom\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;

class EntityMetadataFactoryTest extends TestCase
{
    private DriverInterface|MockObject $driver;
    private MetadataProcessorPipeline|MockObject $metadataProcessor;
    private FileScanner|MockObject $fileScanner;
    private EntityMetadataFactory $factory;
    private EntityMetadataFactory $factoryWithAccessibleMethods;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('laradom.config.metadata.cache', true);
        Config::set('laradom.config.metadata.auto_invalidate_cache', true);

        Cache::flush();

        $this->driver = $this->createMock(DriverInterface::class);
        $this->metadataProcessor = $this->createMock(MetadataProcessorPipeline::class);
        $this->fileScanner = $this->createMock(FileScanner::class);

        $this->factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            true,
        );

        $this->factoryWithAccessibleMethods = $this->getMockBuilder(EntityMetadataFactory::class)
            ->setConstructorArgs([
                $this->driver,
                app(CacheRepository::class),
                $this->metadataProcessor,
                $this->fileScanner,
                true,
                true,
            ])
            ->onlyMethods([])
            ->getMock();
    }

    public function testGetEntityMetadataForNonExistentClassThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('The "NonExistentClass" class was not found. Make sure that the class exists and is available.');

        $this->factory->getEntityMetadata('NonExistentClass');
    }

    public function testGetEntityMetadataFromCache(): void
    {
        $factory = $this->getMockBuilder(EntityMetadataFactory::class)
            ->setConstructorArgs([
                $this->driver,
                app(CacheRepository::class),
                $this->metadataProcessor,
                $this->fileScanner,
                true,
                true,
            ])
            ->onlyMethods(['getAllMetadata'])
            ->getMock();

        $className = self::class;
        $metadata = new EntityMetadata($className, 'cached_table');

        $factory->method('getAllMetadata')
            ->willReturn([$className => $metadata]);

        $this->driver->expects($this->never())
            ->method('extractMetadata');

        $result = $factory->getEntityMetadata($className);

        $this->assertNotNull($result);
        $this->assertEquals('cached_table', $result->getTableName());
    }

    public function testGetEntityMetadataBuildsMetadataWhenNotInCache(): void
    {
        $className = self::class;
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'test_table');

        Cache::forget(EntityMetadataFactory::CACHE_META_DATA);

        $this->driver->method('supports')
            ->with($className)
            ->willReturn(true);

        $this->driver->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $result = $this->factory->getEntityMetadata($className);

        $this->assertEquals('test_table', $result->getTableName());

        $cachedData = Cache::get(EntityMetadataFactory::CACHE_META_DATA);
        $this->assertArrayHasKey($className, $cachedData);
    }

    public function testGetEntityMetadataWithDisabledCache(): void
    {
        $className = self::class;
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'test_table');

        $this->driver->method('supports')
            ->with($className)
            ->willReturn(true);

        $this->driver->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            false,
            true,
        );

        Cache::forget(EntityMetadataFactory::CACHE_META_DATA);

        $result = $factory->getEntityMetadata($className);

        $this->assertEquals('test_table', $result->getTableName());

        $this->assertNull(Cache::get(EntityMetadataFactory::CACHE_META_DATA));
    }

    public function testGetEntityMetadataWhenCacheReturnsInvalidValue(): void
    {
        $className = self::class;
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'test_table');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, []);

        $this->driver->method('supports')
            ->with($className)
            ->willReturn(true);

        $this->driver->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $result = $this->factory->getEntityMetadata($className);

        $this->assertEquals('test_table', $result->getTableName());

        $cachedData = Cache::get(EntityMetadataFactory::CACHE_META_DATA);
        $this->assertIsArray($cachedData);
        $this->assertArrayHasKey($className, $cachedData);
    }

    public function testClearCache(): void
    {
        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, ['class1' => new EntityMetadata('class1')]);
        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, ['file1' => 'checksum1']);
        Cache::forever(EntityMetadataFactory::CACHE_META_CLASSES, ['class1']);

        $this->assertNotNull(Cache::get(EntityMetadataFactory::CACHE_META_DATA));
        $this->assertNotNull(Cache::get(EntityMetadataFactory::CACHE_META_CHECKSUMS));
        $this->assertNotNull(Cache::get(EntityMetadataFactory::CACHE_META_CLASSES));

        $this->factory->clearCache();

        $this->assertNull(Cache::get(EntityMetadataFactory::CACHE_META_DATA));
        $this->assertNull(Cache::get(EntityMetadataFactory::CACHE_META_CHECKSUMS));
        $this->assertNull(Cache::get(EntityMetadataFactory::CACHE_META_CLASSES));
    }

    public function testGetAllMetadata(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';
        $extractedMetadata1 = new EntityMetadata($className1);
        $extractedMetadata2 = new EntityMetadata($className2);
        $processedMetadata1 = new EntityMetadata($className1, 'processed_table_1');
        $processedMetadata2 = new EntityMetadata($className2, 'processed_table_2');

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);

        Cache::forget(EntityMetadataFactory::CACHE_META_DATA);
        Cache::forget(EntityMetadataFactory::CACHE_META_CHECKSUMS);

        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
                [$className2, true],
            ]);

        $this->driver->method('extractMetadata')
            ->willReturnMap([
                [$className1, $extractedMetadata1],
                [$className2, $extractedMetadata2],
            ]);

        $this->metadataProcessor->method('process')
            ->willReturnMap([
                [$extractedMetadata1, $processedMetadata1],
                [$extractedMetadata2, $processedMetadata2],
            ]);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $result = $this->factory->getAllMetadata();

        $this->assertCount(2, $result);
        $this->assertEquals('processed_table_1', $result[$className1]->getTableName());
        $this->assertEquals('processed_table_2', $result[$className2]->getTableName());

        $cachedData = Cache::get(EntityMetadataFactory::CACHE_META_DATA);
        $this->assertIsArray($cachedData);
        $this->assertArrayHasKey($className1, $cachedData);
        $this->assertArrayHasKey($className2, $cachedData);
    }

    public function testGetAllMetadataWithCachedData(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $cachedMetadata1 = new EntityMetadata($className1, 'cached_table_1');
        $cachedMetadata2 = new EntityMetadata($className2, 'cached_table_2');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [
            $className1 => $cachedMetadata1,
            $className2 => $cachedMetadata2,
        ]);

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            false,
        );

        $this->driver->expects($this->never())
            ->method('extractMetadata');

        $result = $factory->getAllMetadata();

        $this->assertCount(2, $result);
        $this->assertEquals('cached_table_1', $result[$className1]->getTableName());
        $this->assertEquals('cached_table_2', $result[$className2]->getTableName());
    }

    public function testGetAllMetadataWithNewFile(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';
        $className3 = 'Entity3';

        $cachedMetadata1 = new EntityMetadata($className1, 'cached_table_1');
        $cachedMetadata2 = new EntityMetadata($className2, 'cached_table_2');

        $extractedMetadata1 = new EntityMetadata($className1);
        $extractedMetadata2 = new EntityMetadata($className2);
        $extractedMetadata3 = new EntityMetadata($className3);

        $processedMetadata1 = new EntityMetadata($className1, 'processed_table_1');
        $processedMetadata2 = new EntityMetadata($className2, 'processed_table_2');
        $processedMetadata3 = new EntityMetadata($className3, 'processed_table_3');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [
            $className1 => $cachedMetadata1,
            $className2 => $cachedMetadata2,
        ]);

        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, [
            '/path/to/entity1.php' => 'checksum1',
            '/path/to/entity2.php' => 'checksum2',
        ]);

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);
        $fileInfo3 = new FileInfo('entity3.php', '/path/to/entity3.php', $className3);

        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2, $fileInfo3]);

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
                [$className2, true],
                [$className3, true],
            ]);

        $this->driver->method('extractMetadata')
            ->willReturnMap([
                [$className1, $extractedMetadata1],
                [$className2, $extractedMetadata2],
                [$className3, $extractedMetadata3],
            ]);

        $this->metadataProcessor->method('process')
            ->willReturnMap([
                [$extractedMetadata1, $processedMetadata1],
                [$extractedMetadata2, $processedMetadata2],
                [$extractedMetadata3, $processedMetadata3],
            ]);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            true,
        );

        $result = $factory->getAllMetadata();

        $this->assertCount(3, $result);
        $this->assertArrayHasKey($className3, $result);
        $this->assertEquals('processed_table_3', $result[$className3]->getTableName());
    }

    public function testGetAllMetadataWithModifiedFile(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $cachedMetadata1 = new EntityMetadata($className1, 'cached_table_1');
        $cachedMetadata2 = new EntityMetadata($className2, 'cached_table_2');

        $extractedMetadata1 = new EntityMetadata($className1);
        $processedMetadata1 = new EntityMetadata($className1, 'updated_table_1');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [
            $className1 => $cachedMetadata1,
            $className2 => $cachedMetadata2,
        ]);

        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, [
            '/path/to/entity1.php' => 'old_checksum1',
            '/path/to/entity2.php' => 'checksum2',
        ]);

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);
        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
                [$className2, true],
            ]);

        $this->driver->method('extractMetadata')
            ->willReturnMap([
                [$className1, $extractedMetadata1],
                [$className2, $cachedMetadata2],
            ]);

        $this->metadataProcessor->method('process')
            ->willReturnMap([
                [$extractedMetadata1, $processedMetadata1],
                [$cachedMetadata2, $cachedMetadata2],
            ]);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            true,
        );

        $result = $factory->getAllMetadata();

        $this->assertCount(2, $result);
        $this->assertEquals('updated_table_1', $result[$className1]->getTableName());
    }

    public function testGetAllMetadataWithRemovedFile(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $cachedMetadata1 = new EntityMetadata($className1, 'cached_table_1');
        $cachedMetadata2 = new EntityMetadata($className2, 'cached_table_2');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [
            $className1 => $cachedMetadata1,
            $className2 => $cachedMetadata2,
        ]);

        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, [
            '/path/to/entity1.php' => 'checksum1',
            '/path/to/entity2.php' => 'checksum2',
        ]);

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1]);

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
            ]);

        $this->driver->method('extractMetadata')
            ->willReturnMap([
                [$className1, $cachedMetadata1],
            ]);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            true,
        );

        $result = $factory->getAllMetadata();

        $this->assertCount(1, $result);
        $this->assertArrayNotHasKey($className2, $result);
    }

    public function testGetAllMetadataWithDisabledAutoInvalidation(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $cachedMetadata1 = new EntityMetadata($className1, 'cached_table_1');
        $cachedMetadata2 = new EntityMetadata($className2, 'cached_table_2');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [
            $className1 => $cachedMetadata1,
            $className2 => $cachedMetadata2,
        ]);

        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, [
            '/path/to/entity1.php' => 'old_checksum1',
            '/path/to/entity2.php' => 'checksum2',
        ]);

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);
        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            false,
        );

        $result = $factory->getAllMetadata();

        $this->assertCount(2, $result);
        $this->assertEquals('cached_table_1', $result[$className1]->getTableName());
        $this->assertEquals('cached_table_2', $result[$className2]->getTableName());
    }

    public function testBuildMetadata(): void
    {
        $className = self::class;
        $extractedMetadata = new EntityMetadata($className);
        $processedMetadata = new EntityMetadata($className, 'test_table');

        $this->driver->method('supports')
            ->with($className)
            ->willReturn(true);

        $this->driver->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $this->metadataProcessor->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'buildMetadata');
        $result = $method->invoke($this->factoryWithAccessibleMethods, $className);

        $this->assertNotNull($result);
        $this->assertEquals('test_table', $result->getTableName());
    }

    public function testBuildMetadataForUnsupportedClass(): void
    {
        $className = self::class;

        $this->driver->method('supports')
            ->with($className)
            ->willReturn(false);

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'buildMetadata');
        $result = $method->invoke($this->factoryWithAccessibleMethods, $className);

        $this->assertNull($result);
    }

    public function testBuildAllMetadata(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);

        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $extractedMetadata1 = new EntityMetadata($className1);
        $extractedMetadata2 = new EntityMetadata($className2);

        $processedMetadata1 = new EntityMetadata($className1, 'table1');
        $processedMetadata2 = new EntityMetadata($className2, 'table2');

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
                [$className2, true],
            ]);

        $this->driver->method('extractMetadata')
            ->willReturnMap([
                [$className1, $extractedMetadata1],
                [$className2, $extractedMetadata2],
            ]);

        $this->metadataProcessor->method('process')
            ->willReturnMap([
                [$extractedMetadata1, $processedMetadata1],
                [$extractedMetadata2, $processedMetadata2],
            ]);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'buildAllMetadata');
        $result = $method->invoke($this->factoryWithAccessibleMethods);

        $this->assertCount(2, $result);
        $this->assertEquals('table1', $result[$className1]->getTableName());
        $this->assertEquals('table2', $result[$className2]->getTableName());
    }

    public function testBuildAllMetadataWithException(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);

        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $extractedMetadata1 = new EntityMetadata($className1);

        $processedMetadata1 = new EntityMetadata($className1, 'table1');

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
                [$className2, false],
            ]);

        $this->driver->method('extractMetadata')
            ->with($className1)
            ->willReturn($extractedMetadata1);

        $this->metadataProcessor->method('process')
            ->with($extractedMetadata1)
            ->willReturn($processedMetadata1);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'buildAllMetadata');
        $result = $method->invoke($this->factoryWithAccessibleMethods);

        $this->assertCount(1, $result);
        $this->assertEquals('table1', $result[$className1]->getTableName());
    }

    public function testShouldInvalidateCache(): void
    {
        Cache::forget(EntityMetadataFactory::CACHE_META_CHECKSUMS);

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'shouldInvalidateCache');

        $result = $method->invoke($this->factoryWithAccessibleMethods);
        $this->assertTrue($result);

        $hasChangesMethod = new ReflectionMethod($this->factoryWithAccessibleMethods, 'hasFileChanges');

        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $currentChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $result = $hasChangesMethod->invoke($this->factoryWithAccessibleMethods, $savedChecksums, $currentChecksums);
        $this->assertFalse($result);

        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, $savedChecksums);

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/file1.php', 'Entity1');
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/file2.php', 'Entity2');
        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $this->driver->method('supports')
            ->willReturn(true);
    }

    public function testGetAllMetadataWithAutoInvalidateCache(): void
    {
        $className = 'TestClass';
        $metadata = new EntityMetadata($className, 'test_table');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [$className => $metadata]);

        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
        ];
        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, $savedChecksums);

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            true,
        );

        $fileInfo = new FileInfo('test.php', '/path/to/file1.php', $className);
        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo]);

        $this->driver->method('supports')
            ->with($className)
            ->willReturn(true);

        $extractedMetadata = new EntityMetadata($className);
        $this->driver->method('extractMetadata')
            ->with($className)
            ->willReturn($extractedMetadata);

        $processedMetadata = new EntityMetadata($className, 'new_table');
        $this->metadataProcessor->method('process')
            ->with($extractedMetadata)
            ->willReturn($processedMetadata);

        $this->metadataProcessor->method('postProcess')
            ->willReturnCallback(function (array $metadataMap) {
                return $metadataMap;
            });

        $result = $factory->getAllMetadata();

        $this->assertCount(1, $result);
        $this->assertEquals('new_table', $result[$className]->getTableName());

        $cachedMetadata = Cache::get(EntityMetadataFactory::CACHE_META_DATA);
        $this->assertEquals('new_table', $cachedMetadata[$className]->getTableName());
    }

    public function testGetAllMetadataWithDisabledAutoInvalidationUsingMock(): void
    {
        $className = 'TestClass';
        $metadata = new EntityMetadata($className, 'test_table');

        Cache::forever(EntityMetadataFactory::CACHE_META_DATA, [$className => $metadata]);

        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
        ];
        Cache::forever(EntityMetadataFactory::CACHE_META_CHECKSUMS, $savedChecksums);

        $factory = new EntityMetadataFactory(
            $this->driver,
            app(CacheRepository::class),
            $this->metadataProcessor,
            $this->fileScanner,
            true,
            false,
        );

        $this->fileScanner->expects($this->never())
            ->method('scan');

        $result = $factory->getAllMetadata();

        $this->assertCount(1, $result);
        $this->assertEquals('test_table', $result[$className]->getTableName());
    }

    public function testCalculateFileChecksums(): void
    {
        $className1 = 'Entity1';
        $className2 = 'Entity2';

        $fileInfo1 = new FileInfo('entity1.php', '/path/to/entity1.php', $className1);
        $fileInfo2 = new FileInfo('entity2.php', '/path/to/entity2.php', $className2);

        $this->fileScanner->method('scan')
            ->willReturn([$fileInfo1, $fileInfo2]);

        $this->driver->method('supports')
            ->willReturnMap([
                [$className1, true],
                [$className2, false],
            ]);

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'calculateFileChecksums');
        $result = $method->invoke($this->factoryWithAccessibleMethods);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('/path/to/entity1.php', $result);
    }

    public function testHasFileChangesWithNewFiles(): void
    {
        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
        ];

        $currentChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'hasFileChanges');
        $result = $method->invoke($this->factoryWithAccessibleMethods, $savedChecksums, $currentChecksums);

        $this->assertTrue($result);
    }

    public function testHasFileChangesWithDeletedFiles(): void
    {
        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $currentChecksums = [
            '/path/to/file1.php' => 'checksum1',
        ];

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'hasFileChanges');
        $result = $method->invoke($this->factoryWithAccessibleMethods, $savedChecksums, $currentChecksums);

        $this->assertTrue($result);
    }

    public function testHasFileChangesWithModifiedFiles(): void
    {
        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $currentChecksums = [
            '/path/to/file1.php' => 'checksum1_modified',
            '/path/to/file2.php' => 'checksum2',
        ];

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'hasFileChanges');
        $result = $method->invoke($this->factoryWithAccessibleMethods, $savedChecksums, $currentChecksums);

        $this->assertTrue($result);
    }

    public function testHasFileChangesWithNoChanges(): void
    {
        $savedChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $currentChecksums = [
            '/path/to/file1.php' => 'checksum1',
            '/path/to/file2.php' => 'checksum2',
        ];

        $method = new ReflectionMethod($this->factoryWithAccessibleMethods, 'hasFileChanges');
        $result = $method->invoke($this->factoryWithAccessibleMethods, $savedChecksums, $currentChecksums);

        $this->assertFalse($result);
    }
}
