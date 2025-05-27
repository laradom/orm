<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Index;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Attributes\UniqueConstraint;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\IndexMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use Laradom\ORM\Mapping\UniqueConstraintMetadata;
use ReflectionClass;
use ReflectionProperty;

class MetadataProcessor
{
    /**
     * @param FieldHandlerInterface[] $fieldHandlers
     * @param RelationHandlerInterface[] $relationHandlers
     */
    public function __construct(
        private readonly array $fieldHandlers = [],
        private readonly array $relationHandlers = [],
    ) {}

    /**
     * @template T of object
     *
     * @param ReflectionClass<T> $class
     */
    public function process(ReflectionClass $class, EntityMetadata $metadata): void
    {
        $this->processClassAttributes($class, $metadata);
        $this->processProperties($class, $metadata);
    }

    /**
     * @template T of object
     *
     * @param ReflectionClass<T> $class
     */
    private function processClassAttributes(ReflectionClass $class, EntityMetadata $metadata): void
    {
        $tableAttributes = $class->getAttributes(Table::class);

        if (!empty($tableAttributes)) {
            $tableAttribute = $tableAttributes[0]->newInstance();
            $metadata->setTableName($tableAttribute->name);
        }

        $indexAttributes = $class->getAttributes(Index::class);
        foreach ($indexAttributes as $attribute) {
            $indexAttribute = $attribute->newInstance();
            $indexMetadata = new IndexMetadata(
                $indexAttribute->name,
                $indexAttribute->columns,
                $indexAttribute->unique,
            );
            $metadata->addIndex($indexMetadata);
        }

        $constraintAttributes = $class->getAttributes(UniqueConstraint::class);
        foreach ($constraintAttributes as $attribute) {
            $constraintAttribute = $attribute->newInstance();
            $name = $constraintAttribute->name;

            if ($name === null) {
                $tableName = $metadata->getTableName() ?? $class->getShortName();
                $name = sprintf('uniq_%s_%s', strtolower($tableName), implode('_', $constraintAttribute->columns));
            }

            $constraintMetadata = new UniqueConstraintMetadata(
                $name,
                $constraintAttribute->columns,
            );
            $metadata->addUniqueConstraint($constraintMetadata);
        }
    }

    /**
     * @template T of object
     *
     * @param ReflectionClass<T> $class
     */
    private function processProperties(ReflectionClass $class, EntityMetadata $metadata): void
    {
        foreach ($class->getProperties() as $property) {
            $hasRelationAttributes = false;

            foreach ($this->relationHandlers as $handler) {
                if ($handler->support($property)) {
                    $hasRelationAttributes = true;
                    break;
                }
            }

            if ($hasRelationAttributes) {
                $this->processRelationProperty($property, $metadata);
            } else {
                $this->processFieldProperty($property, $metadata);
            }
        }
    }

    private function processRelationProperty(
        ReflectionProperty $property,
        EntityMetadata $metadata,
    ): void {
        $relationMetadata = new RelationMetadata();

        foreach ($this->relationHandlers as $handler) {
            if ($handler->support($property)) {
                $handler->handle($property, $relationMetadata);
            }
        }

        $metadata->addRelation($relationMetadata);
    }

    private function processFieldProperty(ReflectionProperty $property, EntityMetadata $metadata): void
    {
        $fieldMetadata = new FieldMetadata();
        $handled = false;

        foreach ($this->fieldHandlers as $handler) {
            if ($handler->support($property)) {
                $handler->handle($property, $fieldMetadata);
                $handled = true;
            }
        }

        if ($handled) {
            $metadata->addField($fieldMetadata);
        }
    }
}
