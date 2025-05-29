<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\Driver\AttributeHandler\FieldHandlerInterface;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessor;
use Laradom\ORM\Mapping\Driver\AttributeHandler\RelationHandlerInterface;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

#[Entity]
#[Table(name: 'test_entity')]
class TestEntity
{
    private int $id;
    private string $name;
    private array $items;
}

class MetadataProcessorTest extends TestCase
{
    private MetadataProcessor $metadataProcessor;
    private FieldHandlerInterface|MockObject $fieldHandler;
    private MockObject|RelationHandlerInterface $relationHandler;
    private EntityMetadata $metadata;
    private ReflectionClass $reflectionClass;

    protected function setUp(): void
    {
        $this->fieldHandler = $this->createMock(FieldHandlerInterface::class);
        $this->relationHandler = $this->createMock(RelationHandlerInterface::class);

        $this->metadataProcessor = new MetadataProcessor(
            [$this->fieldHandler],
            [$this->relationHandler],
        );

        $this->reflectionClass = new ReflectionClass(TestEntity::class);
        $this->metadata = new EntityMetadata(TestEntity::class);
    }

    public function testProcessClassAttributes(): void
    {
        $this->metadataProcessor->process($this->reflectionClass, $this->metadata);

        $this->assertEquals('test_entity', $this->metadata->getTableName());
    }

    public function testProcessFields(): void
    {
        $idProperty = $this->reflectionClass->getProperty('id');
        $nameProperty = $this->reflectionClass->getProperty('name');

        $this->fieldHandler->expects($this->exactly(2))
            ->method('support')
            ->willReturnCallback(function (ReflectionProperty $property) {
                if ($property->getName() === 'id' || $property->getName() === 'name') {
                    return true;
                }

                return false;
            });

        $this->fieldHandler->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function (ReflectionProperty $property, FieldMetadata $fieldMetadata) {
                if ($property->getName() === 'id') {
                    $fieldMetadata->setPropertyName('id');
                    $fieldMetadata->setColumnName('id');
                    $fieldMetadata->setType(Types::INTEGER);
                    $fieldMetadata->setIsPrimaryKey(true);
                } elseif ($property->getName() === 'name') {
                    $fieldMetadata->setPropertyName('name');
                    $fieldMetadata->setColumnName('name');
                    $fieldMetadata->setType(Types::STRING);
                }
            });

        $this->relationHandler->expects($this->exactly(4))
            ->method('support')
            ->willReturnCallback(function (ReflectionProperty $property) {
                if ($property->getName() === 'items') {
                    return true;
                }

                return false;
            });

        $this->relationHandler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (ReflectionProperty $property, RelationMetadata $relationMetadata) {
                $relationMetadata->setType(RelationTypes::OneToMany);
                $relationMetadata->setFieldName('items');
                $relationMetadata->setTargetEntity('TestTargetEntity');
            });

        $this->metadataProcessor->process($this->reflectionClass, $this->metadata);

        $this->assertCount(2, $this->metadata->getFields());
        $this->assertCount(1, $this->metadata->getRelations());

        $fields = $this->metadata->getFields();
        $this->assertEquals('id', $fields[0]->getPropertyName());
        $this->assertEquals('name', $fields[1]->getPropertyName());

        $relations = $this->metadata->getRelations();
        $this->assertEquals('items', $relations[0]->getFieldName());
        $this->assertEquals(RelationTypes::OneToMany, $relations[0]->getType());
        $this->assertEquals('TestTargetEntity', $relations[0]->getTargetEntity());
    }
}
