<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TableNameProcessorTest extends TestCase
{
    private MockObject|NamingStrategyInterface $namingStrategy;
    private TableNameProcessor $processor;

    protected function setUp(): void
    {
        $this->namingStrategy = $this->createMock(NamingStrategyInterface::class);
        $this->processor = new TableNameProcessor($this->namingStrategy);
    }

    public function testProcessWithExistingTableName(): void
    {
        $metadata = new EntityMetadata('TestEntity', 'existing_table');

        $this->namingStrategy->expects($this->never())
            ->method('classToTableName');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('existing_table', $result->getTableName());
    }

    public function testProcessWithoutTableName(): void
    {
        $className = 'TestEntity';
        $expectedTableName = 'test_entities';
        $metadata = new EntityMetadata($className);

        $this->namingStrategy->expects($this->once())
            ->method('classToTableName')
            ->with($className)
            ->willReturn($expectedTableName);

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals($expectedTableName, $result->getTableName());
    }
}
