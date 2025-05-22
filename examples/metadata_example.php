<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessorFactory;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Processor\ColumnTypeProcessor;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\IndexNameProcessor;
use Laradom\ORM\Mapping\Processor\JoinColumnProcessor;
use Laradom\ORM\Mapping\Processor\JoinTableProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\PostProcessor\BidirectionalRelationshipPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\CascadeOperationsPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\MetadataValidationPostProcessor;
use Laradom\ORM\Mapping\Processor\PrimaryKeyProcessor;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Mapping\Processor\UniqueConstraintNameProcessor;
use Laradom\ORM\Scanning\FileScanner;

require_once __DIR__ . '/../vendor/autoload.php';

$filesystem = new Filesystem();
$store = new ArrayStore();
$cache = new CacheRepository($store);
$entityScanner = new FileScanner($filesystem, [__DIR__ . '/Entities']);

$namingStrategy = new DefaultNamingStrategy();

$metadataFactory = new MetadataProcessorFactory();
$attributeDriver = new AttributeDriver($metadataFactory->create());

$metadataProcessorPipeline = new MetadataProcessorPipeline([
    new TableNameProcessor($namingStrategy),
    new FieldNameProcessor($namingStrategy),
    new IndexNameProcessor($namingStrategy),
    new UniqueConstraintNameProcessor($namingStrategy),
    new PrimaryKeyProcessor($namingStrategy),
    new ColumnTypeProcessor($namingStrategy),
    new JoinColumnProcessor($namingStrategy),
    new JoinTableProcessor($namingStrategy),
], [
    new BidirectionalRelationshipPostProcessor(),
    new CascadeOperationsPostProcessor(),
    new MetadataValidationPostProcessor(),
]);

$entityMetadataFactory = new EntityMetadataFactory(
    $attributeDriver,
    $cache,
    $metadataProcessorPipeline,
    $entityScanner,
    true,
    true,
);

$entities = $entityMetadataFactory->getAllMetadata();
echo 'Found entities: ' . count($entities) . "\n";

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

            $cascadeType = $relation->getCascade();

            $cascadeTypes = [];

            if ($cascadeType->isPersist()) {
                $cascadeTypes[] = 'PERSIST';
            }

            if ($cascadeType->isRemove()) {
                $cascadeTypes[] = 'REMOVE';
            }

            if ($cascadeType->isRefresh()) {
                $cascadeTypes[] = 'REFRESH';
            }

            if ($cascadeType->isMerge()) {
                $cascadeTypes[] = 'MERGE';
            }

            if ($cascadeType->isAll()) {
                $cascadeTypes[] = 'ALL';
            }

            if (count($cascadeTypes) > 0) {
                echo ' [CASCADE: ' . implode(', ', $cascadeTypes) . ']';
            }

            if ($relation->isOrphanRemoval()) {
                echo ' [ORPHAN REMOVAL]';
            }

            echo "\n";
        }
    } else {
        echo "\nRelations: none\n";
    }

    if (count($metadata->getIndexes()) > 0) {
        echo "\nIndexes:\n";
        foreach ($metadata->getIndexes() as $index) {
            echo "- {$index->getName()}";
            echo ' (' . implode(', ', $index->getColumns()) . ')';

            if ($index->isUnique()) {
                echo ' [UNIQUE]';
            }

            echo "\n";
        }
    }

    if (count($metadata->getUniqueConstraints()) > 0) {
        echo "\nUnique Constraints:\n";
        foreach ($metadata->getUniqueConstraints() as $constraint) {
            echo "- {$constraint->getName()}";
            echo ' (' . implode(', ', $constraint->getColumns()) . ')';
            echo "\n";
        }
    }
}
