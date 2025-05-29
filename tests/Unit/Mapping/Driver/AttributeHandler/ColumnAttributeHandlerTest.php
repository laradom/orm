<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ColumnAttributeHandler;
use Laradom\ORM\Mapping\FieldMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ColumnAttributeHandlerTest extends TestCase
{
    private ColumnAttributeHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ColumnAttributeHandler();
    }

    public function testGetAttributeClass(): void
    {
        $this->assertEquals(Column::class, $this->handler->getAttributeClass());
    }

    public function testSupportWithColumnAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithColumn::class);
        $property = $reflectionClass->getProperty('name');

        $this->assertTrue($this->handler->support($property));
    }

    public function testSupportWithoutColumnAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithoutColumn::class);
        $property = $reflectionClass->getProperty('name');

        $this->assertFalse($this->handler->support($property));
    }

    public function testHandleWithColumnAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithColumn::class);
        $property = $reflectionClass->getProperty('name');
        $fieldMetadata = new FieldMetadata();

        $this->handler->handle($property, $fieldMetadata);

        $this->assertEquals('name', $fieldMetadata->getPropertyName());
        $this->assertEquals('user_name', $fieldMetadata->getColumnName());
        $this->assertEquals(Types::STRING, $fieldMetadata->getType());
        $this->assertEquals(255, $fieldMetadata->getLength());
        $this->assertTrue($fieldMetadata->isNullable());
    }

    public function testHandleWithoutColumnAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithoutColumn::class);
        $property = $reflectionClass->getProperty('name');
        $fieldMetadata = new FieldMetadata();
        $fieldMetadata->setPropertyName('initialName');
        $fieldMetadata->setColumnName('initial_column');
        $fieldMetadata->setType(Types::INTEGER);
        $fieldMetadata->setLength(10);
        $fieldMetadata->setNullable(false);

        $this->handler->handle($property, $fieldMetadata);

        $this->assertEquals('initialName', $fieldMetadata->getPropertyName());
        $this->assertEquals('initial_column', $fieldMetadata->getColumnName());
        $this->assertEquals(Types::INTEGER, $fieldMetadata->getType());
        $this->assertEquals(10, $fieldMetadata->getLength());
        $this->assertFalse($fieldMetadata->isNullable());
    }
}

class TestEntityWithColumn
{
    #[Column(name: 'user_name', type: Types::STRING, length: 255, nullable: true)]
    private string $name;
}

class TestEntityWithoutColumn
{
    private string $name;
}
