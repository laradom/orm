<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessorFactory;
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
use Laradom\ORM\Util\Naming\DefaultNamingStrategy;

require_once __DIR__ . '/../vendor/autoload.php';

$filesystem = new Filesystem();
$store = new ArrayStore();
$cache = new CacheRepository($store);
$entityScanner = new FileScanner($filesystem, [__DIR__ . '/../tests/Integration/Mapping/TestEntity']);

$inflector = new EnglishInflectorStrategy();
$namingStrategy = new DefaultNamingStrategy();

$metadataFactory = new MetadataProcessorFactory();
$attributeDriver = new AttributeDriver($metadataFactory->create());

$metadataProcessorPipeline = new MetadataProcessorPipeline([
    new TableNameProcessor($namingStrategy, $inflector),
    new FieldNameProcessor($namingStrategy),
    new PrimaryKeyProcessor(),
    new ColumnTypeProcessor(),
], [
    new BidirectionalRelationshipPostProcessor(),
    new CascadeOperationsPostProcessor(),
    new JoinTablePostProcessor($namingStrategy, $inflector),
    new JoinColumnPostProcessor($namingStrategy, $inflector),
    new IndexNamePostProcessor(),
    new UniqueConstraintNamePostProcessor(),
]);

$entityMetadataFactory = new EntityMetadataFactory(
    $attributeDriver,
    $cache,
    $metadataProcessorPipeline,
    $entityScanner,
    true,
    true,
);

$tableNameProcessor = new TableNameProcessor($namingStrategy, $inflector);
$fieldNameProcessor = new FieldNameProcessor($namingStrategy);
$primaryKeyProcessor = new PrimaryKeyProcessor();
$columnTypeProcessor = new ColumnTypeProcessor();

$bidirectionalProcessor = new BidirectionalRelationshipPostProcessor();
$cascadeOperationsProcessor = new CascadeOperationsPostProcessor();
$joinTableProcessor = new JoinTablePostProcessor($namingStrategy, $inflector);
$joinColumnProcessor = new JoinColumnPostProcessor($namingStrategy, $inflector);
$indexNameProcessor = new IndexNamePostProcessor();
$uniqueConstraintNameProcessor = new UniqueConstraintNamePostProcessor();

Laradom\Benchmarks\BenchmarkContext::setComponents([
    'entityMetadataFactory' => $entityMetadataFactory,
    'cache' => $cache,
    'namingStrategy' => $namingStrategy,
    'inflector' => $inflector,
    'fileScanner' => $entityScanner,
    'filesystem' => $filesystem,
    'tableNameProcessor' => $tableNameProcessor,
    'fieldNameProcessor' => $fieldNameProcessor,
    'primaryKeyProcessor' => $primaryKeyProcessor,
    'columnTypeProcessor' => $columnTypeProcessor,
    'bidirectionalProcessor' => $bidirectionalProcessor,
    'cascadeOperationsProcessor' => $cascadeOperationsProcessor,
    'joinTableProcessor' => $joinTableProcessor,
    'joinColumnProcessor' => $joinColumnProcessor,
    'indexNameProcessor' => $indexNameProcessor,
    'uniqueConstraintNameProcessor' => $uniqueConstraintNameProcessor,
]);
