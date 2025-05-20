<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessorFactory;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Scanning\FileScanner;

require_once __DIR__ . '/../vendor/autoload.php';

$filesystem = new Filesystem();

// $store = new FileStore($filesystem, '../var/cache');
$store = new ArrayStore();
$cache = new CacheRepository($store);

$entityScanner = new FileScanner($filesystem, [__DIR__ . '/Entities']);

$namingStrategy = new DefaultNamingStrategy();

$metadataProcessorPipeline = new MetadataProcessorPipeline([
    new TableNameProcessor($namingStrategy),
    new FieldNameProcessor($namingStrategy),
]);

$metadataFactory = new MetadataProcessorFactory();

$attributeDriver = new AttributeDriver($metadataFactory->create());

$entityMetadataFactory = new EntityMetadataFactory(
    $attributeDriver,
    $cache,
    $metadataProcessorPipeline,
    $entityScanner,
    true,
    true,
);

$entities = $entityMetadataFactory->getAllMetadata();

echo 'Find entities: ' . count($entities) . "\n";

foreach ($entities as $entity) {
    displayEntityMetadata($entity);
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

function displayEntityMetadata(EntityMetadata $metadata): void
{
    echo "Entity: {$metadata->getClassName()}\n";
    echo "Table: {$metadata->getTableName()}\n";

    echo "\nFields:\n";
    foreach ($metadata->getFields() as $field) {
        echo "- {$field->getPropertyName()} ({$field->getType()->value})";

        if ($field->isPrimaryKey()) {
            echo ' [PK]';
        }

        if (!empty($field->getGeneratedFieldMetadata()?->isGenerated())) {
            echo ' [Generated]';
        }

        echo "\n";
    }

    if (count($metadata->getRelations()) > 0) {
        echo "\nRelations:\n";
        foreach ($metadata->getRelations() as $relation) {
            echo "- {$relation->getFieldName()} ({$relation->getType()->value})";
            echo " -> {$relation->getTargetEntity()}";

            if ($relation->getMappedBy() !== null) {
                echo " (mappedBy: {$relation->getMappedBy()})";
            }

            if ($relation->getInversedBy() !== null) {
                echo " (inversedBy: {$relation->getInversedBy()})";
            }

            if ($relation->isCascadePersist()) {
                echo ' [CASCADE PERSIST]';
            }

            if ($relation->isOrphanRemoval()) {
                echo ' [ORPHAN REMOVAL]';
            }

            echo "\n";
        }
    } else {
        echo "\nRelations: none\n";
    }
}
