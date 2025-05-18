<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Enum\GeneratorType\GeneratorType;
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

require_once __DIR__ . '/../vendor/autoload.php';

#[Entity]
#[Table('users')]
class UserExample
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::IDENTITY)]
    private int $id;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private string $fullName;
}

$store = new ArrayStore();
$cache = new CacheRepository($store);

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
    true
);

$entityMetadata = $entityMetadataFactory->getEntityMetadata(UserExample::class);
dd($entityMetadata);
