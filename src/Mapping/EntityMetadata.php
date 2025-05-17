<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

class EntityMetadata
{
    public function __construct(
        private string $className,
        private ?string $tableName = null,
        /** @var FieldMetadata[] */
        private array $fields = [],
        /** @var RelationMetadata[] */
        private array $relations = [],
    ) {}

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setTableName(?string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return FieldMetadata[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function addField(FieldMetadata $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * @return RelationMetadata[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function addRelation(RelationMetadata $relation): void
    {
        $this->relations[] = $relation;
    }
}
