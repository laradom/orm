<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping;

use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\Tests\TestCase;

class MetadataProcessorIntegrationTest extends TestCase
{
    private TableNameProcessor $tableNameProcessor;
    private FieldNameProcessor $fieldNameProcessor;
    private MetadataProcessorPipeline $pipeline;

    protected function setUp(): void
    {
        $namingStrategy = new DefaultNamingStrategy();
        $this->tableNameProcessor = new TableNameProcessor($namingStrategy);
        $this->fieldNameProcessor = new FieldNameProcessor($namingStrategy);

        $this->pipeline = new MetadataProcessorPipeline([
            $this->tableNameProcessor,
            $this->fieldNameProcessor,
        ]);
    }

    public function testProcessEntityWithoutTableNameAndFieldsWithoutColumnNames(): void
    {
        $idField = $this->createFieldMetadata('id', Types::INTEGER);
        $nameField = $this->createFieldMetadata('userName');

        $metadata = new EntityMetadata('UserProfile');
        $metadata->addField($idField);
        $metadata->addField($nameField);

        $result = $this->pipeline->process($metadata);

        $this->assertEquals('user_profile', $result->getTableName());

        $this->assertEquals('id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('user_name', $result->getFields()[1]->getColumnName());
    }

    public function testProcessEntityWithTableNameAndFieldsWithoutColumnNames(): void
    {
        $idField = $this->createFieldMetadata('id', Types::INTEGER);
        $nameField = $this->createFieldMetadata('userName');

        $metadata = new EntityMetadata('UserProfile', 'custom_users');
        $metadata->addField($idField);
        $metadata->addField($nameField);

        $result = $this->pipeline->process($metadata);

        $this->assertEquals('custom_users', $result->getTableName());

        $this->assertEquals('id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('user_name', $result->getFields()[1]->getColumnName());
    }

    public function testProcessEntityWithoutTableNameAndFieldsWithColumnNames(): void
    {
        $idField = $this->createFieldMetadata('id', Types::INTEGER, 'custom_id');
        $nameField = $this->createFieldMetadata('userName', Types::STRING, 'custom_user_name');

        $metadata = new EntityMetadata('UserProfile');
        $metadata->addField($idField);
        $metadata->addField($nameField);

        $result = $this->pipeline->process($metadata);

        $this->assertEquals('user_profile', $result->getTableName());

        $this->assertEquals('custom_id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('custom_user_name', $result->getFields()[1]->getColumnName());
    }

    public function testProcessEntityWithMixedFields(): void
    {
        $idField = $this->createFieldMetadata('id', Types::INTEGER, 'custom_id');
        $nameField = $this->createFieldMetadata('userName', Types::STRING);
        $emailField = $this->createFieldMetadata('emailAddress', Types::STRING);

        $metadata = new EntityMetadata('UserProfile');
        $metadata->addField($idField);
        $metadata->addField($nameField);
        $metadata->addField($emailField);

        $result = $this->pipeline->process($metadata);

        $this->assertEquals('user_profile', $result->getTableName());

        $this->assertEquals('custom_id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('user_name', $result->getFields()[1]->getColumnName());
        $this->assertEquals('email_address', $result->getFields()[2]->getColumnName());
    }

    public function testProcessWithChangedProcessorOrder(): void
    {
        $idField = $this->createFieldMetadata('id', Types::INTEGER);
        $nameField = $this->createFieldMetadata('userName');

        $metadata = new EntityMetadata('UserProfile');
        $metadata->addField($idField);
        $metadata->addField($nameField);

        $reversedPipeline = new MetadataProcessorPipeline([
            $this->fieldNameProcessor,
            $this->tableNameProcessor,
        ]);

        $result = $reversedPipeline->process($metadata);

        $this->assertEquals('user_profile', $result->getTableName());
        $this->assertEquals('id', $result->getFields()[0]->getColumnName());
        $this->assertEquals('user_name', $result->getFields()[1]->getColumnName());
    }

    private function createFieldMetadata(string $propertyName, Types $type = Types::STRING, ?string $columnName = null): FieldMetadata
    {
        $field = new FieldMetadata();
        $field->setPropertyName($propertyName);
        $field->setType($type);
        $field->setNullable(false);
        $field->setIsPrimaryKey(false);
        $field->setColumnName($columnName);

        return $field;
    }
}
