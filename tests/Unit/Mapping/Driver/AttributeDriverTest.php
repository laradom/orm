<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessor;
use Laradom\ORM\Mapping\FieldMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AttributeDriverTest extends TestCase
{
    private AttributeDriver $driver;
    private MetadataProcessor|MockObject $metadataProcessor;

    protected function setUp(): void
    {
        $this->metadataProcessor = $this->createMock(MetadataProcessor::class);
        $this->driver = new AttributeDriver($this->metadataProcessor);
    }

    public function testExtractMetadataWithEntityAndTableAttributes(): void
    {
        $this->metadataProcessor->expects($this->once())
            ->method('process')
            ->willReturnCallback(function (ReflectionClass $class, $metadata) {
                $metadata->setTableName('custom_users');

                $idField = new FieldMetadata();
                $idField->setPropertyName('id');
                $idField->setColumnName('id');
                $idField->setType(Types::INTEGER);
                $idField->setIsPrimaryKey(true);
                $metadata->addField($idField);

                $nameField = new FieldMetadata();
                $nameField->setPropertyName('name');
                $nameField->setColumnName('user_name');
                $nameField->setType(Types::STRING);
                $nameField->setLength(255);
                $nameField->setNullable(false);
                $metadata->addField($nameField);
            });

        $metadata = $this->driver->extractMetadata(TestEntityWithTableAttribute::class);

        $this->assertEquals(TestEntityWithTableAttribute::class, $metadata->getClassName());
        $this->assertEquals('custom_users', $metadata->getTableName());
        $this->assertCount(2, $metadata->getFields());

        $fields = $metadata->getFields();
        $this->assertEquals('id', $fields[0]->getPropertyName());
        $this->assertEquals('id', $fields[0]->getColumnName());
        $this->assertEquals(Types::INTEGER, $fields[0]->getType());
        $this->assertTrue($fields[0]->isPrimaryKey());

        $this->assertEquals('name', $fields[1]->getPropertyName());
        $this->assertEquals('user_name', $fields[1]->getColumnName());
        $this->assertEquals(Types::STRING, $fields[1]->getType());
        $this->assertEquals(255, $fields[1]->getLength());
        $this->assertFalse($fields[1]->isNullable());
    }

    public function testExtractMetadataWithEntityWithoutTableAttribute(): void
    {
        $this->metadataProcessor->expects($this->once())
            ->method('process')
            ->willReturnCallback(function (ReflectionClass $class, $metadata) {
                $idField = new FieldMetadata();
                $idField->setPropertyName('id');
                $idField->setColumnName('id');
                $idField->setType(Types::INTEGER);
                $idField->setIsPrimaryKey(true);
                $metadata->addField($idField);
            });

        $metadata = $this->driver->extractMetadata(TestEntityWithoutTableAttribute::class);

        $this->assertEquals(TestEntityWithoutTableAttribute::class, $metadata->getClassName());
        $this->assertNull($metadata->getTableName());
        $this->assertCount(1, $metadata->getFields());

        $fields = $metadata->getFields();
        $this->assertEquals('id', $fields[0]->getPropertyName());
        $this->assertEquals('id', $fields[0]->getColumnName());
        $this->assertEquals(Types::INTEGER, $fields[0]->getType());
        $this->assertTrue($fields[0]->isPrimaryKey());
    }

    public function testExtractMetadataWithNonEntityClassThrowsException(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('The "' . TestNonEntity::class . '" class does not have the "' . Entity::class . '" attribute.');

        $this->driver->extractMetadata(TestNonEntity::class);
    }
}

#[Entity]
#[Table(name: 'custom_users')]
class TestEntityWithTableAttribute
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    private int $id;

    #[Column(name: 'user_name', length: 255)]
    private string $name;
}

#[Entity]
class TestEntityWithoutTableAttribute
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    private int $id;
}

class TestNonEntity
{
    private int $id;
}
