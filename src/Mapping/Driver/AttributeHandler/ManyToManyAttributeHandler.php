<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

class ManyToManyAttributeHandler extends AbstractAttributeHandler implements RelationHandlerInterface
{
    public function getAttributeClass(): string
    {
        return ManyToMany::class;
    }

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        /** @var ManyToMany|null $attribute */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $relationMetadata->setType(RelationTypes::ManyToMany);
        $relationMetadata->setFieldName($property->getName());
        $relationMetadata->setTargetEntity($attribute->targetEntity);

        if ($attribute->mappedBy !== null) {
            $relationMetadata->setMappedBy($attribute->mappedBy);
        }

        if ($attribute->inversedBy !== null) {
            $relationMetadata->setInversedBy($attribute->inversedBy);
        }

        $relationMetadata->setCascadePersist($attribute->cascadePersist);
        $relationMetadata->setOrphanRemoval($attribute->orphanRemoval);
    }
}
