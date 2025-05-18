<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use PHPUnit\Framework\TestCase;

class EntityMetadataTest extends TestCase
{
    public function testConstructorWithClassNameOnly(): void
    {
        $className = 'TestEntity';
        $metadata = new EntityMetadata($className);

        $this->assertEquals($className, $metadata->getClassName());
        $this->assertNull($metadata->getTableName());
        $this->assertEmpty($metadata->getFields());
        $this->assertEmpty($metadata->getRelations());
    }

    public function testConstructorWithAllParameters(): void
    {
        $className = 'TestEntity';
        $tableName = 'test_entities';
        $field = $this->createMock(FieldMetadata::class);
        $relation = $this->createMock(RelationMetadata::class);

        $metadata = new EntityMetadata($className, $tableName, [$field], [$relation]);

        $this->assertEquals($className, $metadata->getClassName());
        $this->assertEquals($tableName, $metadata->getTableName());
        $this->assertCount(1, $metadata->getFields());
        $this->assertSame($field, $metadata->getFields()[0]);
        $this->assertCount(1, $metadata->getRelations());
        $this->assertSame($relation, $metadata->getRelations()[0]);
    }

    public function testSetClassName(): void
    {
        $metadata = new EntityMetadata('InitialClass');
        $metadata->setClassName('NewClass');

        $this->assertEquals('NewClass', $metadata->getClassName());
    }

    public function testSetTableName(): void
    {
        $metadata = new EntityMetadata('TestEntity');
        $metadata->setTableName('custom_table');

        $this->assertEquals('custom_table', $metadata->getTableName());
    }

    public function testAddField(): void
    {
        $metadata = new EntityMetadata('TestEntity');
        $field1 = $this->createMock(FieldMetadata::class);
        $field2 = $this->createMock(FieldMetadata::class);

        $metadata->addField($field1);
        $metadata->addField($field2);

        $fields = $metadata->getFields();
        $this->assertCount(2, $fields);
        $this->assertSame($field1, $fields[0]);
        $this->assertSame($field2, $fields[1]);
    }

    public function testAddRelation(): void
    {
        $metadata = new EntityMetadata('TestEntity');
        $relation1 = $this->createMock(RelationMetadata::class);
        $relation2 = $this->createMock(RelationMetadata::class);

        $metadata->addRelation($relation1);
        $metadata->addRelation($relation2);

        $relations = $metadata->getRelations();
        $this->assertCount(2, $relations);
        $this->assertSame($relation1, $relations[0]);
        $this->assertSame($relation2, $relations[1]);
    }
}
