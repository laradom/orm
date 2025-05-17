<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;

require_once __DIR__ . '/../vendor/autoload.php';

#[Entity]
#[Table('users')]
class User {}

$store = new ArrayStore();
$fileCache = new Repository($store);

$namingStrategy = new DefaultNamingStrategy();

$metadataProcessor = new MetadataProcessorPipeline([
    new TableNameProcessor($namingStrategy),
]);

$entityMetadataFactory = new EntityMetadataFactory(
    new AttributeDriver(),
    $fileCache,
    $metadataProcessor,
    true
);

$entityMetadata = $entityMetadataFactory->getEntityMetadata(User::class);
dd($entityMetadata);
