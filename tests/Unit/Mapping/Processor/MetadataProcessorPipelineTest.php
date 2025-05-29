<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Processor\MetadataProcessorInterface;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use PHPUnit\Framework\TestCase;

class MetadataProcessorPipelineTest extends TestCase
{
    public function testProcessWithEmptyProcessors(): void
    {
        $metadata = new EntityMetadata('TestEntity');
        $pipeline = new MetadataProcessorPipeline([]);

        $result = $pipeline->process($metadata);

        $this->assertSame($metadata, $result);
    }

    public function testProcessWithSingleProcessor(): void
    {
        $metadata = new EntityMetadata('TestEntity');
        $expectedMetadata = new EntityMetadata('TestEntity', 'test_entities');

        $processor = $this->createMock(MetadataProcessorInterface::class);
        $processor->expects($this->once())
            ->method('process')
            ->with($metadata)
            ->willReturn($expectedMetadata);

        $pipeline = new MetadataProcessorPipeline([$processor]);

        $result = $pipeline->process($metadata);

        $this->assertSame($expectedMetadata, $result);
    }

    public function testProcessWithMultipleProcessors(): void
    {
        $initialMetadata = new EntityMetadata('TestEntity');
        $intermediateMetadata = new EntityMetadata('TestEntity', 'intermediate_table');
        $finalMetadata = new EntityMetadata('TestEntity', 'final_table');

        $processor1 = $this->createMock(MetadataProcessorInterface::class);
        $processor1->expects($this->once())
            ->method('process')
            ->with($initialMetadata)
            ->willReturn($intermediateMetadata);

        $processor2 = $this->createMock(MetadataProcessorInterface::class);
        $processor2->expects($this->once())
            ->method('process')
            ->with($intermediateMetadata)
            ->willReturn($finalMetadata);

        $pipeline = new MetadataProcessorPipeline([$processor1, $processor2]);

        $result = $pipeline->process($initialMetadata);

        $this->assertSame($finalMetadata, $result);
    }

    public function testProcessExecutesProcessorsInOrder(): void
    {
        $metadata = new EntityMetadata('TestEntity');

        $processor1 = $this->getMockBuilder(MetadataProcessorInterface::class)
            ->getMock();
        $processor1->expects($this->once())
            ->method('process')
            ->with($metadata)
            ->willReturnCallback(function (EntityMetadata $metadata) {
                $metadata->setTableName('table_1');

                return $metadata;
            });

        $processor2 = $this->getMockBuilder(MetadataProcessorInterface::class)
            ->getMock();
        $processor2->expects($this->once())
            ->method('process')
            ->willReturnCallback(function (EntityMetadata $metadata) {
                $this->assertEquals('table_1', $metadata->getTableName());
                $metadata->setTableName('table_2');

                return $metadata;
            });

        $processor3 = $this->getMockBuilder(MetadataProcessorInterface::class)
            ->getMock();
        $processor3->expects($this->once())
            ->method('process')
            ->willReturnCallback(function (EntityMetadata $metadata) {
                $this->assertEquals('table_2', $metadata->getTableName());
                $metadata->setTableName('table_3');

                return $metadata;
            });

        $pipeline = new MetadataProcessorPipeline([$processor1, $processor2, $processor3]);

        $result = $pipeline->process($metadata);

        $this->assertEquals('table_3', $result->getTableName());
    }
}
