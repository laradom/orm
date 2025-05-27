<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Util\Inflector\InflectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TableNameProcessorTest extends TestCase
{
    private MockObject|NamingStrategyInterface $namingStrategy;
    private MockObject|InflectorInterface $inflector;
    private TableNameProcessor $processor;

    protected function setUp(): void
    {
        $this->namingStrategy = $this->createMock(NamingStrategyInterface::class);
        $this->inflector = $this->createMock(InflectorInterface::class);
        $this->processor = new TableNameProcessor($this->namingStrategy, $this->inflector);
    }

    public function testProcessWithExistingTableName(): void
    {
        $metadata = new EntityMetadata('TestEntity', 'existing_table');
        
        $this->inflector->expects($this->once())
            ->method('pluralize')
            ->with('existing_table')
            ->willReturn('existing_table');
            
        $this->namingStrategy->expects($this->once())
            ->method('classToTableName')
            ->with('existing_table')
            ->willReturn('existing_table');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('existing_table', $result->getTableName());
    }

    public function testProcessWithoutTableName(): void
    {
        $metadata = new EntityMetadata('TestEntity');

        $this->inflector->expects($this->once())
            ->method('pluralize')
            ->with('TestEntity')
            ->willReturn('TestEntities');
            
        $this->namingStrategy->expects($this->once())
            ->method('classToTableName')
            ->with('TestEntities')
            ->willReturn('test_entities');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('test_entities', $result->getTableName());
    }
}
