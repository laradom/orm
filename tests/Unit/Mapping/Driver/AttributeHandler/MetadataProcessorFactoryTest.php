<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Mapping\Driver\AttributeHandler\ColumnAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\FieldHandlerInterface;
use Laradom\ORM\Mapping\Driver\AttributeHandler\GeneratedValueAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\IdAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\JoinColumnAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\JoinTableAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ManyToManyAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ManyToOneAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessor;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessorFactory;
use Laradom\ORM\Mapping\Driver\AttributeHandler\OneToManyAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\OneToOneAttributeHandler;
use Laradom\ORM\Mapping\Driver\AttributeHandler\RelationHandlerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MetadataProcessorFactoryTest extends TestCase
{
    private MetadataProcessorFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new MetadataProcessorFactory();
    }

    public function testCreateFieldHandlers(): void
    {
        $handlers = $this->factory->createFieldHandlers();

        $this->assertCount(3, $handlers);
        $this->assertContainsOnlyInstancesOf(FieldHandlerInterface::class, $handlers);

        $handlerClasses = array_map(fn ($handler) => get_class($handler), $handlers);
        $this->assertContains(ColumnAttributeHandler::class, $handlerClasses);
        $this->assertContains(IdAttributeHandler::class, $handlerClasses);
        $this->assertContains(GeneratedValueAttributeHandler::class, $handlerClasses);
    }

    public function testCreateRelationHandlers(): void
    {
        $handlers = $this->factory->createRelationHandlers();

        $this->assertCount(6, $handlers);
        $this->assertContainsOnlyInstancesOf(RelationHandlerInterface::class, $handlers);

        $handlerClasses = array_map(fn ($handler) => get_class($handler), $handlers);
        $this->assertContains(OneToOneAttributeHandler::class, $handlerClasses);
        $this->assertContains(OneToManyAttributeHandler::class, $handlerClasses);
        $this->assertContains(ManyToOneAttributeHandler::class, $handlerClasses);
        $this->assertContains(ManyToManyAttributeHandler::class, $handlerClasses);
        $this->assertContains(JoinColumnAttributeHandler::class, $handlerClasses);
        $this->assertContains(JoinTableAttributeHandler::class, $handlerClasses);
    }

    public function testCreateWithDefaults(): void
    {
        $processor = $this->factory->create();

        $this->assertInstanceOf(MetadataProcessor::class, $processor);

        $reflection = new ReflectionClass($processor);

        $fieldHandlersProperty = $reflection->getProperty('fieldHandlers');
        $fieldHandlers = $fieldHandlersProperty->getValue($processor);

        $relationHandlersProperty = $reflection->getProperty('relationHandlers');
        $relationHandlers = $relationHandlersProperty->getValue($processor);

        $this->assertCount(3, $fieldHandlers);
        $this->assertCount(6, $relationHandlers);
    }

    public function testCreateWithCustomHandlers(): void
    {
        $mockFieldHandler = $this->createMock(FieldHandlerInterface::class);
        $mockRelationHandler = $this->createMock(RelationHandlerInterface::class);

        $processor = $this->factory->create([$mockFieldHandler], [$mockRelationHandler]);

        $reflection = new ReflectionClass($processor);

        $fieldHandlersProperty = $reflection->getProperty('fieldHandlers');
        $fieldHandlers = $fieldHandlersProperty->getValue($processor);

        $relationHandlersProperty = $reflection->getProperty('relationHandlers');
        $relationHandlers = $relationHandlersProperty->getValue($processor);

        $this->assertCount(1, $fieldHandlers);
        $this->assertSame($mockFieldHandler, $fieldHandlers[0]);

        $this->assertCount(1, $relationHandlers);
        $this->assertSame($mockRelationHandler, $relationHandlers[0]);
    }
}
