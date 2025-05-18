<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Enum\GeneratorType\GeneratorType;
use Laradom\ORM\Exception\InvalidArgumentException;
use Laradom\ORM\Mapping\Driver\AttributeHandler\GeneratedValueAttributeHandler;
use Laradom\ORM\Mapping\FieldMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GeneratedValueAttributeHandlerTest extends TestCase
{
    private GeneratedValueAttributeHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new GeneratedValueAttributeHandler();
    }

    public function testGetAttributeClass(): void
    {
        $this->assertEquals(GeneratedValue::class, $this->handler->getAttributeClass());
    }

    public function testSupportWithGeneratedValueAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithGeneratedValue::class);
        $property = $reflectionClass->getProperty('id');

        $this->assertTrue($this->handler->support($property));
    }

    public function testSupportWithoutGeneratedValueAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithoutGeneratedValue::class);
        $property = $reflectionClass->getProperty('id');

        $this->assertFalse($this->handler->support($property));
    }

    public function testHandleWithGeneratedValueAttributeAndIdAttribute(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithGeneratedValue::class);
        $property = $reflectionClass->getProperty('id');
        $fieldMetadata = new FieldMetadata();

        $this->handler->handle($property, $fieldMetadata);

        $generatedFieldMetadata = $fieldMetadata->generated();
        $this->assertNotNull($generatedFieldMetadata);
        $this->assertTrue($generatedFieldMetadata->isGenerated());
        $this->assertEquals(GeneratorType::IDENTITY, $generatedFieldMetadata->getGeneratorType());
        $this->assertNull($generatedFieldMetadata->getGeneratedCustomClass());
    }

    public function testHandleWithGeneratedValueAttributeAndCustomStrategy(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithCustomGeneratedValue::class);
        $property = $reflectionClass->getProperty('id');
        $fieldMetadata = new FieldMetadata();

        $this->handler->handle($property, $fieldMetadata);

        $generatedFieldMetadata = $fieldMetadata->generated();
        $this->assertNotNull($generatedFieldMetadata);
        $this->assertTrue($generatedFieldMetadata->isGenerated());
        $this->assertEquals(GeneratorType::CUSTOM, $generatedFieldMetadata->getGeneratorType());
        $this->assertEquals('App\CustomGenerator', $generatedFieldMetadata->getGeneratedCustomClass());
    }

    public function testHandleWithoutIdAttributeThrowsException(): void
    {
        $reflectionClass = new ReflectionClass(TestEntityWithGeneratedValueButNoId::class);
        $property = $reflectionClass->getProperty('id');
        $fieldMetadata = new FieldMetadata();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "GeneratedValue" attribute can only be used on id fields.');

        $this->handler->handle($property, $fieldMetadata);
    }
}

class TestEntityWithGeneratedValue
{
    #[Id]
    #[GeneratedValue]
    private int $id;
}

class TestEntityWithCustomGeneratedValue
{
    #[Id]
    #[GeneratedValue(strategy: GeneratorType::CUSTOM, customClass: 'App\CustomGenerator')]
    private int $id;
}

class TestEntityWithoutGeneratedValue
{
    #[Id]
    private int $id;
}

class TestEntityWithGeneratedValueButNoId
{
    #[GeneratedValue]
    private int $id;
}
