<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
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
    }

    /**
     * @template T of object
     *
     * @param ReflectionClass<T> $class
     */
    private function processProperties(ReflectionClass $class, EntityMetadata $metadata): void
    {
        foreach ($class->getProperties() as $property) {
            $relationHandler = $this->findRelationHandler($property);

            if ($relationHandler !== null) {
                $this->processRelationProperty($property, $metadata, $relationHandler);
            } else {
                $this->processFieldProperty($property, $metadata);
            }
        }
    }

    private function processRelationProperty(
        ReflectionProperty $property,
        EntityMetadata $metadata,
        RelationHandlerInterface $handler,
    ): void {
        $relationMetadata = new RelationMetadata();
        $handler->handle($property, $relationMetadata);
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

    private function findRelationHandler(ReflectionProperty $property): ?RelationHandlerInterface
    {
        foreach ($this->relationHandlers as $handler) {
            if ($handler->support($property)) {
                return $handler;
            }
        }

        return null;
    }
}
