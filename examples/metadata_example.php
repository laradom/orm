<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;
use Laradom\Examples\Entities\UserExample;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\AttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ColumnAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\GeneratedValueAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\IdAttributeHandler;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Scanning\FileScanner;

require_once __DIR__ . '/../vendor/autoload.php';

$filesystem = new Filesystem();

$store = new FileStore($filesystem, '../var/cache');
//$store = new ArrayStore();
$cache = new CacheRepository($store);

$entityScanner = new FileScanner($filesystem, [__DIR__ . '/../tests/Entities',]);

$namingStrategy = new DefaultNamingStrategy();

$metadataProcessor = new MetadataProcessorPipeline([
    new TableNameProcessor($namingStrategy),
    new FieldNameProcessor($namingStrategy),
]);

$attributeHandler = new AttributeHandler([
    new IdAttributeHandler(),
    new GeneratedValueAttributeHandler(),
    new ColumnAttributeHandler(),
]);

$attributeDriver = new AttributeDriver($attributeHandler);

$entityMetadataFactory = new EntityMetadataFactory(
    $attributeDriver,
    $cache,
    $metadataProcessor,
    $entityScanner,
    true,
    true
);

$entities = $entityMetadataFactory->getAllMetadata();

echo "Found entities:\n";
foreach ($entities as $entity) {
    echo "- {$entity->getClassName()}\n";
}

$metadata = $entityMetadataFactory->getEntityMetadata(UserExample::class);
if ($metadata === null) {
    echo 'Metadata not found';
    exit(1);
}

echo "\nEntity: {$metadata->getClassName()}\n";
echo "Table: {$metadata->getTableName()}\n";
echo "\nFields:\n";

foreach ($metadata->getFields() as $field) {
    echo "- {$field->getPropertyName()} ({$field->getType()->value})";

    if ($field->isPrimaryKey()) {
        echo " [PK]";
    }

    if (!empty($field->getGeneratedFieldMetadata()?->isGenerated())) {
        echo " [Generated]";
    }

    echo "\n";
}
