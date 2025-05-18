<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Mapping\Driver\AttributeHandler\IdAttributeHandler;
use Laradom\ORM\Mapping\FieldMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class IdAttributeHandlerTest extends TestCase
{
    private IdAttributeHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new IdAttributeHandler();
    }

    public function testGetAttributeClass(): void
    {
        $this->assertEquals(Id::class, $this->handler->getAttributeClass());
    }

    public function testSupportWithIdAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithId::class);
        $property = $reflectionClass->getProperty('id');

        $this->assertTrue($this->handler->support($property));
    }

    public function testSupportWithoutIdAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithoutId::class);
        $property = $reflectionClass->getProperty('id');

        $this->assertFalse($this->handler->support($property));
    }

    public function testHandleWithIdAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithId::class);
        $property = $reflectionClass->getProperty('id');
        $fieldMetadata = new FieldMetadata();
        $fieldMetadata->setIsId(false);

        $this->handler->handle($property, $fieldMetadata);

        $this->assertTrue($fieldMetadata->isId());
    }

    public function testHandleWithoutIdAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithoutId::class);
        $property = $reflectionClass->getProperty('id');
        $fieldMetadata = new FieldMetadata();
        $fieldMetadata->setIsId(false);

        $this->handler->handle($property, $fieldMetadata);

        $this->assertFalse($fieldMetadata->isId());
    }
}

class TestEntityWithId
{
    #[Id]
    private int $id;
}

class TestEntityWithoutId
{
    private int $id;
}
