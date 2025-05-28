<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Laradom\ORM\LaradomServiceProvider;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Mapping\Processor\ColumnTypeProcessor;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\PostProcessor\BidirectionalRelationshipPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\CascadeOperationsPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\IndexNamePostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\JoinColumnPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\JoinTablePostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\UniqueConstraintNamePostProcessor;
use Laradom\ORM\Mapping\Processor\PrimaryKeyProcessor;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Util\Inflector\EnglishInflector;
use Laradom\ORM\Util\Inflector\InflectorInterface;
use Laradom\Tests\TestCase;
use ReflectionClass;

class LaradomServiceProviderTest extends TestCase
{
    public function testServiceProviderRegistration(): void
    {
        $this->assertTrue($this->app->bound(DriverInterface::class));
        $this->assertTrue($this->app->bound(NamingStrategyInterface::class));
        $this->assertTrue($this->app->bound(MetadataProcessorPipeline::class));
        $this->assertTrue($this->app->bound(EntityMetadataFactory::class));

        $this->assertTrue($this->app->bound(InflectorInterface::class));
        $this->assertTrue($this->app->bound(TableNameProcessor::class));
        $this->assertTrue($this->app->bound(FieldNameProcessor::class));
        $this->assertTrue($this->app->bound(PrimaryKeyProcessor::class));
        $this->assertTrue($this->app->bound(ColumnTypeProcessor::class));

        $this->assertTrue($this->app->bound(BidirectionalRelationshipPostProcessor::class));
        $this->assertTrue($this->app->bound(CascadeOperationsPostProcessor::class));
        $this->assertTrue($this->app->bound(JoinTablePostProcessor::class));
        $this->assertTrue($this->app->bound(JoinColumnPostProcessor::class));
        $this->assertTrue($this->app->bound(IndexNamePostProcessor::class));
        $this->assertTrue($this->app->bound(UniqueConstraintNamePostProcessor::class));
    }

    public function testEntityMetadataFactoryIsConfiguredCorrectly(): void
    {
        $factory = $this->app->make(EntityMetadataFactory::class);

        $this->assertInstanceOf(EntityMetadataFactory::class, $factory);

        $reflection = new ReflectionClass($factory);
        $cacheProperty = $reflection->getProperty('cache');

        $this->assertInstanceOf(CacheRepository::class, $cacheProperty->getValue($factory));

        $cacheEnabledProperty = $reflection->getProperty('cacheEnabled');

        $this->assertTrue($cacheEnabledProperty->getValue($factory));
    }

    public function testConfigIsPublished(): void
    {
        $this->assertTrue(config('laradom.config.metadata.cache'));
        $this->assertIsArray(config('laradom.config.entity_paths'));
        $this->assertEquals(AttributeDriver::class, config('laradom.config.metadata.driver'));
        $this->assertEquals(DefaultNamingStrategy::class, config('laradom.config.naming_strategy'));
    }

    public function testMetadataProcessorPipelineIsConfiguredCorrectly(): void
    {
        $pipeline = $this->app->make(MetadataProcessorPipeline::class);
        $this->assertInstanceOf(MetadataProcessorPipeline::class, $pipeline);

        $reflection = new ReflectionClass($pipeline);
        $processorsProperty = $reflection->getProperty('processors');
        $processors = $processorsProperty->getValue($pipeline);

        $this->assertCount(4, $processors);
        $this->assertInstanceOf(TableNameProcessor::class, $processors[0]);
        $this->assertInstanceOf(FieldNameProcessor::class, $processors[1]);
        $this->assertInstanceOf(PrimaryKeyProcessor::class, $processors[2]);
        $this->assertInstanceOf(ColumnTypeProcessor::class, $processors[3]);

        $postProcessorsProperty = $reflection->getProperty('postProcessors');
        $postProcessors = $postProcessorsProperty->getValue($pipeline);

        $this->assertCount(6, $postProcessors);
        $this->assertInstanceOf(BidirectionalRelationshipPostProcessor::class, $postProcessors[0]);
        $this->assertInstanceOf(CascadeOperationsPostProcessor::class, $postProcessors[1]);
        $this->assertInstanceOf(JoinTablePostProcessor::class, $postProcessors[2]);
        $this->assertInstanceOf(JoinColumnPostProcessor::class, $postProcessors[3]);
        $this->assertInstanceOf(IndexNamePostProcessor::class, $postProcessors[4]);
        $this->assertInstanceOf(UniqueConstraintNamePostProcessor::class, $postProcessors[5]);
    }

    public function testProcessorsAreConfiguredCorrectly(): void
    {
        $tableNameProcessor = $this->app->make(TableNameProcessor::class);
        $this->assertInstanceOf(TableNameProcessor::class, $tableNameProcessor);

        $fieldNameProcessor = $this->app->make(FieldNameProcessor::class);
        $this->assertInstanceOf(FieldNameProcessor::class, $fieldNameProcessor);

        $primaryKeyProcessor = $this->app->make(PrimaryKeyProcessor::class);
        $this->assertInstanceOf(PrimaryKeyProcessor::class, $primaryKeyProcessor);

        $columnTypeProcessor = $this->app->make(ColumnTypeProcessor::class);
        $this->assertInstanceOf(ColumnTypeProcessor::class, $columnTypeProcessor);
    }

    public function testPostProcessorsAreConfiguredCorrectly(): void
    {
        $joinColumnPostProcessor = $this->app->make(JoinColumnPostProcessor::class);
        $this->assertInstanceOf(JoinColumnPostProcessor::class, $joinColumnPostProcessor);

        $joinTablePostProcessor = $this->app->make(JoinTablePostProcessor::class);
        $this->assertInstanceOf(JoinTablePostProcessor::class, $joinTablePostProcessor);
    }

    public function testNamingStrategyIsConfiguredCorrectly(): void
    {
        $namingStrategy = $this->app->make(NamingStrategyInterface::class);
        $this->assertInstanceOf(DefaultNamingStrategy::class, $namingStrategy);
    }

    public function testInflectorIsConfiguredCorrectly(): void
    {
        $inflector = $this->app->make(InflectorInterface::class);
        $this->assertInstanceOf(EnglishInflector::class, $inflector);
    }

    public function testMetadataCacheCanBeDisabled(): void
    {
        config(['laradom.config.metadata.cache' => false]);

        $this->app->forgetInstance(EntityMetadataFactory::class);
        $factory = $this->app->make(EntityMetadataFactory::class);

        $reflection = new ReflectionClass($factory);
        $cacheEnabledProperty = $reflection->getProperty('cacheEnabled');

        $this->assertFalse($cacheEnabledProperty->getValue($factory));
    }

    public function testEntityPathsConfiguration(): void
    {
        $customPaths = ['/custom/path1', '/custom/path2'];
        config(['laradom.config.entity_paths' => $customPaths]);

        $this->assertEquals($customPaths, config('laradom.config.entity_paths'));
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaradomServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('laradom.config.metadata.cache', true);
        $app['config']->set('laradom.config.entity_paths', [__DIR__ . '/Entities']);
        $app['config']->set('laradom.config.metadata.driver', AttributeDriver::class);
        $app['config']->set('laradom.config.naming_strategy', DefaultNamingStrategy::class);
    }
}
