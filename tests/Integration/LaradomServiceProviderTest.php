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
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Orchestra\Testbench\TestCase;
use ReflectionClass;

class LaradomServiceProviderTest extends TestCase
{
    public function testServiceProviderRegistration(): void
    {
        $this->assertTrue($this->app->bound(DriverInterface::class));
        $this->assertTrue($this->app->bound(NamingStrategyInterface::class));
        $this->assertTrue($this->app->bound(MetadataProcessorPipeline::class));
        $this->assertTrue($this->app->bound(EntityMetadataFactory::class));
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

    public function testDriverIsConfiguredCorrectly(): void
    {
        $driver = $this->app->make(DriverInterface::class);
        $this->assertInstanceOf(AttributeDriver::class, $driver);
    }

    public function testNamingStrategyIsConfiguredCorrectly(): void
    {
        $namingStrategy = $this->app->make(NamingStrategyInterface::class);
        $this->assertInstanceOf(DefaultNamingStrategy::class, $namingStrategy);
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
