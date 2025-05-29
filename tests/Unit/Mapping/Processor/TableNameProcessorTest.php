<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Util\Inflector\InflectorStrategyInterface;
use Laradom\ORM\Util\Naming\NamingStrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TableNameProcessorTest extends TestCase
{
    private MockObject|NamingStrategyInterface $namingStrategy;
    private InflectorStrategyInterface|MockObject $inflector;
    private TableNameProcessor $processor;

    protected function setUp(): void
    {
        $this->namingStrategy = $this->createMock(NamingStrategyInterface::class);
        $this->inflector = $this->createMock(InflectorStrategyInterface::class);
        $this->processor = new TableNameProcessor($this->namingStrategy, $this->inflector);
    }

    public function testProcessWithExistingTableName(): void
    {
        $metadata = new EntityMetadata('TestEntity', 'test_entities');

        $this->inflector->expects($this->never())
            ->method('pluralize');

        $this->namingStrategy->expects($this->never())
            ->method('classToTableName');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('test_entities', $result->getTableName());
    }

    public function testProcessWithoutTableName(): void
    {
        $metadata = new EntityMetadata('TestEntity');

        $this->inflector->expects($this->once())
            ->method('pluralize')
            ->with('test_entities')
            ->willReturn('test_entities');

        $this->namingStrategy->expects($this->once())
            ->method('classToTableName')
            ->with('TestEntity')
            ->willReturn('test_entities');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('test_entities', $result->getTableName());
    }
}
