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
        private ?FieldMetadata $primaryKey = null,
        /** @var IndexMetadata[] */
        private array $indexes = [],
        /** @var UniqueConstraintMetadata[] */
        private array $uniqueConstraints = [],
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

        if ($field->isPrimaryKey()) {
            $this->primaryKey = $field;
        }
    }

    public function getFieldByPropertyName(string $propertyName): ?FieldMetadata
    {
        foreach ($this->fields as $field) {
            if ($field->getPropertyName() === $propertyName) {
                return $field;
            }
        }

        return null;
    }

    public function getFieldByColumnName(string $columnName): ?FieldMetadata
    {
        foreach ($this->fields as $field) {
            if ($field->getColumnName() === $columnName) {
                return $field;
            }
        }

        return null;
    }

    public function getPrimaryKey(): ?FieldMetadata
    {
        return $this->primaryKey;
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

    public function getRelationByFieldName(string $fieldName): ?RelationMetadata
    {
        foreach ($this->relations as $relation) {
            if ($relation->getFieldName() === $fieldName) {
                return $relation;
            }
        }

        return null;
    }

    public function getRelationsByTargetEntity(string $targetEntity): array
    {
        $relations = [];

        foreach ($this->relations as $relation) {
            if ($relation->getTargetEntity() === $targetEntity) {
                $relations[] = $relation;
            }
        }

        return $relations;
    }

    /**
     * @return IndexMetadata[]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function addIndex(IndexMetadata $index): void
    {
        $this->indexes[] = $index;
    }

    public function getIndexByName(string $name): ?IndexMetadata
    {
        foreach ($this->indexes as $index) {
            if ($index->getName() === $name) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return UniqueConstraintMetadata[]
     */
    public function getUniqueConstraints(): array
    {
        return $this->uniqueConstraints;
    }

    public function addUniqueConstraint(UniqueConstraintMetadata $constraint): void
    {
        $this->uniqueConstraints[] = $constraint;
    }

    public function getUniqueConstraintByName(string $name): ?UniqueConstraintMetadata
    {
        foreach ($this->uniqueConstraints as $constraint) {
            if ($constraint->getName() === $name) {
                return $constraint;
            }
        }

        return null;
    }
}
