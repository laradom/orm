<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping;

use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\GeneratedFieldMetadata;
use PHPUnit\Framework\TestCase;

class FieldMetadataTest extends TestCase
{
    private FieldMetadata $fieldMetadata;

    protected function setUp(): void
    {
        $this->fieldMetadata = new FieldMetadata();
    }

    public function testSetAndGetColumnName(): void
    {
        $this->fieldMetadata->setColumnName('user_name');
        $this->assertEquals('user_name', $this->fieldMetadata->getColumnName());
    }

    public function testSetAndGetPropertyName(): void
    {
        $this->fieldMetadata->setPropertyName('userName');
        $this->assertEquals('userName', $this->fieldMetadata->getPropertyName());
    }

    public function testSetAndGetType(): void
    {
        $this->fieldMetadata->setType(Types::STRING);
        $this->assertEquals(Types::STRING, $this->fieldMetadata->getType());

        $this->fieldMetadata->setType(Types::INTEGER);
        $this->assertEquals(Types::INTEGER, $this->fieldMetadata->getType());
    }

    public function testSetAndGetLength(): void
    {
        $this->fieldMetadata->setLength(255);
        $this->assertEquals(255, $this->fieldMetadata->getLength());

        $this->fieldMetadata->setLength(null);
        $this->assertNull($this->fieldMetadata->getLength());
    }

    public function testSetAndGetNullable(): void
    {
        $this->fieldMetadata->setNullable(true);
        $this->assertTrue($this->fieldMetadata->isNullable());

        $this->fieldMetadata->setNullable(false);
        $this->assertFalse($this->fieldMetadata->isNullable());
    }

    public function testSetAndGetIsId(): void
    {
        $this->fieldMetadata->setIsPrimaryKey(true);
        $this->assertTrue($this->fieldMetadata->isPrimaryKey());

        $this->fieldMetadata->setIsPrimaryKey(false);
        $this->assertFalse($this->fieldMetadata->isPrimaryKey());
    }

    public function testSetAndGetGeneratedFieldMetadata(): void
    {
        $generatedFieldMetadata = new GeneratedFieldMetadata();
        $this->fieldMetadata->setGeneratedFieldMetadata($generatedFieldMetadata);

        $this->assertSame($generatedFieldMetadata, $this->fieldMetadata->getGeneratedFieldMetadata());
    }
}
