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
use Laradom\ORM\Scanning\FileScanner;
use Laradom\ORM\Util\Inflector\EnglishInflectorStrategy;
use Laradom\ORM\Util\Inflector\InflectorStrategyInterface;
use Laradom\ORM\Util\Naming\DefaultNamingStrategy;
use Laradom\ORM\Util\Naming\NamingStrategyInterface;
use Throwable;

class LaradomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerUtilities();
        $this->registerDrivers();
        $this->registerProcessors();
        $this->registerMetadataFactory();
        $this->registerFileScanner();
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

    private function registerUtilities(): void
    {
        $this->app->singleton(NamingStrategyInterface::class, function () {
            return new (config('laradom.config.naming_strategy', DefaultNamingStrategy::class))();
        });

        $this->app->singleton(InflectorStrategyInterface::class, function () {
            return new (config('laradom.config.inflector_strategy', EnglishInflectorStrategy::class))();
        });
    }

    private function registerDrivers(): void
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
    }

    private function registerProcessors(): void
    {
        $this->app->singleton(TableNameProcessor::class, function ($app) {
            return new TableNameProcessor(
                $app->make(NamingStrategyInterface::class),
                $app->make(InflectorStrategyInterface::class),
            );
        });

        $this->app->singleton(FieldNameProcessor::class, function ($app) {
            return new FieldNameProcessor(
                $app->make(NamingStrategyInterface::class),
            );
        });

        $this->app->singleton(PrimaryKeyProcessor::class, function () {
            return new PrimaryKeyProcessor();
        });

        $this->app->singleton(ColumnTypeProcessor::class, function () {
            return new ColumnTypeProcessor();
        });

        $this->app->singleton(BidirectionalRelationshipPostProcessor::class, function () {
            return new BidirectionalRelationshipPostProcessor();
        });

        $this->app->singleton(CascadeOperationsPostProcessor::class, function () {
            return new CascadeOperationsPostProcessor();
        });

        $this->app->singleton(JoinTablePostProcessor::class, function ($app) {
            return new JoinTablePostProcessor(
                $app->make(NamingStrategyInterface::class),
                $app->make(InflectorStrategyInterface::class),
            );
        });

        $this->app->singleton(JoinColumnPostProcessor::class, function ($app) {
            return new JoinColumnPostProcessor(
                $app->make(NamingStrategyInterface::class),
                $app->make(InflectorStrategyInterface::class),
            );
        });

        $this->app->singleton(IndexNamePostProcessor::class, function () {
            return new IndexNamePostProcessor();
        });

        $this->app->singleton(UniqueConstraintNamePostProcessor::class, function () {
            return new UniqueConstraintNamePostProcessor();
        });

        $this->app->singleton(MetadataProcessorPipeline::class, function ($app) {
            return new MetadataProcessorPipeline([
                $app->make(TableNameProcessor::class),
                $app->make(FieldNameProcessor::class),
                $app->make(PrimaryKeyProcessor::class),
                $app->make(ColumnTypeProcessor::class),
            ], [
                $app->make(BidirectionalRelationshipPostProcessor::class),
                $app->make(CascadeOperationsPostProcessor::class),
                $app->make(JoinTablePostProcessor::class),
                $app->make(JoinColumnPostProcessor::class),
                $app->make(IndexNamePostProcessor::class),
                $app->make(UniqueConstraintNamePostProcessor::class),
            ]);
        });
    }

    private function registerMetadataFactory(): void
    {
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
    }

    private function registerFileScanner(): void
    {
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
}
