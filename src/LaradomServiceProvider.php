<?php

declare(strict_types=1);

namespace Laradom\ORM;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Laradom\ORM\Console\Commands\ClearCacheCommand;
use Laradom\ORM\Console\Commands\ScanEntitiesCommand;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessor;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessorFactory;
use Laradom\ORM\Mapping\Driver\DriverInterface;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Util\Inflector\EnglishInflector;
use Laradom\ORM\Util\Inflector\InflectorInterface;
use Laradom\ORM\Scanning\FileScanner;
use Throwable;

class LaradomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MetadataProcessorFactory::class, function () {
            return new MetadataProcessorFactory();
        });

        $this->app->singleton(MetadataProcessor::class, function ($app) {
            return $app->make(MetadataProcessorFactory::class)->create();
        });

        $this->app->singleton(AttributeDriver::class, function ($app) {
            return new AttributeDriver(
                $app->make(MetadataProcessor::class),
            );
        });

        $this->app->singleton(DriverInterface::class, function ($app) {
            $driverClassName = config('laradom.config.metadata.driver', AttributeDriver::class);

            return $app->make($driverClassName);
        });

        $this->app->singleton(NamingStrategyInterface::class, function () {
            return new (config('laradom.config.naming_strategy', DefaultNamingStrategy::class))();
        });
        
        $this->app->singleton(InflectorInterface::class, function () {
            return new EnglishInflector();
        });

        $this->app->singleton(MetadataProcessorPipeline::class, function ($app) {
            return new MetadataProcessorPipeline([
                $app->make(TableNameProcessor::class),
                $app->make(FieldNameProcessor::class),
            ]);
        });

        $this->app->singleton(EntityMetadataFactory::class, function ($app) {
            $cacheEnabled = (bool) config('laradom.config.metadata.cache', false);
            $autoInvalidateCache = (bool) config('laradom.config.metadata.auto_invalidate_cache', true);

            return new EntityMetadataFactory(
                $app->make(DriverInterface::class),
                $app->make(CacheRepository::class),
                $app->make(MetadataProcessorPipeline::class),
                $app->make(FileScanner::class),
                $cacheEnabled,
                $autoInvalidateCache,
            );
        });

        $this->app->singleton(FileScanner::class, function ($app) {
            /** @var array<class-string> $entityPaths */
            $entityPaths = (array) config('laradom.config.entity_paths', []);

            return new FileScanner(
                $app->make(Filesystem::class),
                $entityPaths,
                $app->make('log'),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laradom.php' => config_path('laradom.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/laradom.php', 'laradom');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanEntitiesCommand::class,
                ClearCacheCommand::class,
            ]);
        }

        if (config('laradom.config.metadata.eager_load_metadata', false)) {
            $this->app->afterResolving(EntityMetadataFactory::class, function (EntityMetadataFactory $factory) {
                try {
                    $factory->getAllMetadata();
                } catch (Throwable $e) {
                    if ($this->app->hasDebugModeEnabled()) {
                        throw $e;
                    }
                }
            });
        }
    }
}
