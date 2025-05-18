<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Mapping\Driver\AttributeHandler\AttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\AttributeHandlerInterface;
use Laradom\ORM\Mapping\FieldMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class AttributeHandlerTest extends TestCase
{
    private AttributeHandler $attributeHandler;
    private AttributeHandlerInterface|MockObject $handler1;
    private AttributeHandlerInterface|MockObject $handler2;
    private ReflectionProperty $property;
    private FieldMetadata $fieldMetadata;

    protected function setUp(): void
    {
        $this->handler1 = $this->createMock(AttributeHandlerInterface::class);
        $this->handler2 = $this->createMock(AttributeHandlerInterface::class);

        $this->attributeHandler = new AttributeHandler([$this->handler1, $this->handler2]);

        $this->property = new ReflectionProperty(TestClass::class, 'property');
        $this->fieldMetadata = new FieldMetadata();
    }

    public function testHandleWithNoSupportingHandlers(): void
    {
        $this->handler1->expects($this->once())
            ->method('support')
            ->with($this->property)
            ->willReturn(false);

        $this->handler2->expects($this->once())
            ->method('support')
            ->with($this->property)
            ->willReturn(false);

        $this->handler1->expects($this->never())
            ->method('handle');

        $this->handler2->expects($this->never())
            ->method('handle');

        $this->attributeHandler->handle($this->property, $this->fieldMetadata);
    }

    public function testHandleWithOneSupportingHandler(): void
    {
        $this->handler1->expects($this->once())
            ->method('support')
            ->with($this->property)
            ->willReturn(true);

        $this->handler2->expects($this->once())
            ->method('support')
            ->with($this->property)
            ->willReturn(false);

        $this->handler1->expects($this->once())
            ->method('handle')
            ->with($this->property, $this->fieldMetadata);

        $this->handler2->expects($this->never())
            ->method('handle');

        $this->attributeHandler->handle($this->property, $this->fieldMetadata);
    }

    public function testHandleWithMultipleSupportingHandlers(): void
    {
        $this->handler1->expects($this->once())
            ->method('support')
            ->with($this->property)
            ->willReturn(true);

        $this->handler2->expects($this->once())
            ->method('support')
            ->with($this->property)
            ->willReturn(true);

        $this->handler1->expects($this->once())
            ->method('handle')
            ->with($this->property, $this->fieldMetadata);

        $this->handler2->expects($this->once())
            ->method('handle')
            ->with($this->property, $this->fieldMetadata);

        $this->attributeHandler->handle($this->property, $this->fieldMetadata);
    }
}

class TestClass
{
    public string $property;
}
