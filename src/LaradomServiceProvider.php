<?php

declare(strict_types=1);

namespace Laradom\ORM;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\AttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ColumnAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\GeneratedValueAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\IdAttributeHandler;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;

class LaradomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AttributeHandler::class, function ($app) {
            return new AttributeHandler([
                $app->make(IdAttributeHandler::class),
                $app->make(GeneratedValueAttributeHandler::class),
                $app->make(ColumnAttributeHandler::class),
            ]);
        });

        $this->app->singleton(AttributeDriver::class, function ($app) {
            return new AttributeDriver(
                $app->make(AttributeHandler::class),
            );
        });

        $this->app->singleton(DriverInterface::class, function ($app) {
            $driverClassName = config('laradom.config.metadata.driver', AttributeDriver::class);

            return $app->make($driverClassName);
        });

        $this->app->singleton(NamingStrategyInterface::class, function () {
            return new (config('laradom.config.naming_strategy', DefaultNamingStrategy::class))();
        });

        $this->app->singleton(MetadataProcessorPipeline::class, function ($app) {
            return new MetadataProcessorPipeline([
                $app->make(TableNameProcessor::class),
                $app->make(FieldNameProcessor::class),
            ]);
        });

        $this->app->singleton(EntityMetadataFactory::class, function ($app) {
            $cacheEnabled = (bool) config('laradom.config.metadata.cache', false);

            return new EntityMetadataFactory(
                $app->make(DriverInterface::class),
                $app->make(CacheRepository::class),
                $app->make(MetadataProcessorPipeline::class),
                $cacheEnabled,
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laradom.php' => config_path('laradom.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/laradom.php', 'laradom');
    }
}
