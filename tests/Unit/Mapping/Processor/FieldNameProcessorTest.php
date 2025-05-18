<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Processor;

use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldNameProcessorTest extends TestCase
{
    private MockObject|NamingStrategyInterface $namingStrategy;
    private FieldNameProcessor $processor;

    protected function setUp(): void
    {
        $this->namingStrategy = $this->createMock(NamingStrategyInterface::class);
        $this->processor = new FieldNameProcessor($this->namingStrategy);
    }

    public function testProcessWithFieldsHavingColumnNames(): void
    {
        $field1 = $this->createFieldMetadata('id', 'existing_id');
        $field2 = $this->createFieldMetadata('userName', 'existing_user_name');

        $metadata = new EntityMetadata('TestEntity');
        $metadata->addField($field1);
        $metadata->addField($field2);

        $this->namingStrategy->expects($this->never())
            ->method('propertyToColumnName');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('existing_id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('existing_user_name', $result->getFields()[1]->getColumnName());
    }

    public function testProcessWithFieldsWithoutColumnNames(): void
    {
        $field1 = $this->createFieldMetadata('id');
        $field2 = $this->createFieldMetadata('userName');

        $metadata = new EntityMetadata('TestEntity');
        $metadata->addField($field1);
        $metadata->addField($field2);

        $this->namingStrategy->expects($this->exactly(2))
            ->method('propertyToColumnName')
            ->willReturnMap([
                ['id', 'id'],
                ['userName', 'user_name'],
            ]);

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('user_name', $result->getFields()[1]->getColumnName());
    }

    public function testProcessWithMixedFields(): void
    {
        $field1 = $this->createFieldMetadata('id', 'existing_id');
        $field2 = $this->createFieldMetadata('userName');

        $metadata = new EntityMetadata('TestEntity');
        $metadata->addField($field1);
        $metadata->addField($field2);

        $this->namingStrategy->expects($this->once())
            ->method('propertyToColumnName')
            ->with('userName')
            ->willReturn('user_name');

        $result = $this->processor->process($metadata);

        $this->assertSame($metadata, $result);
        $this->assertEquals('existing_id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('user_name', $result->getFields()[1]->getColumnName());
    }

    private function createFieldMetadata(string $propertyName, ?string $columnName = null): FieldMetadata
    {
        $field = new FieldMetadata();
        $field->setPropertyName($propertyName);
        $field->setType(Types::STRING);
        $field->setNullable(false);
        $field->setIsId(false);

        if ($columnName !== null) {
            $field->setColumnName($columnName);
        } else {
            $field->setColumnName(null);
        }

        return $field;
    }
}
