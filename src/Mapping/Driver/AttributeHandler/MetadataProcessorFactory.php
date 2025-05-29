<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

class MetadataProcessorFactory
{
    /**
     * @param FieldHandlerInterface[] $fieldHandlers
     * @param RelationHandlerInterface[] $relationHandlers
     */
    public function create(array $fieldHandlers = [], array $relationHandlers = []): MetadataProcessor
    {
        return new MetadataProcessor(
            $fieldHandlers ?: $this->createFieldHandlers(),
            $relationHandlers ?: $this->createRelationHandlers(),
        );
    }

    /**
     * @return FieldHandlerInterface[]
     */
    public function createFieldHandlers(): array
    {
        return [
            new ColumnAttributeHandler(),
            new IdAttributeHandler(),
            new GeneratedValueAttributeHandler(),
        ];
    }

    /**
     * @return RelationHandlerInterface[]
     */
    public function createRelationHandlers(): array
    {
        return [
            new OneToOneAttributeHandler(),
            new OneToManyAttributeHandler(),
            new ManyToOneAttributeHandler(),
            new ManyToManyAttributeHandler(),
            new JoinColumnAttributeHandler(),
            new JoinTableAttributeHandler(),
        ];
    }
}
