<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

class OneToManyAttributeHandler extends AbstractAttributeHandler implements RelationHandlerInterface
{
    public function getAttributeClass(): string
    {
        return OneToMany::class;
    }

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        /** @var OneToMany|null $attribute */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $relationMetadata->setType(RelationTypes::OneToMany);
        $relationMetadata->setFieldName($property->getName());
        $relationMetadata->setTargetEntity($attribute->targetEntity);

        $relationMetadata->setMappedBy($attribute->mappedBy);
        $relationMetadata->setCascadePersist($attribute->cascadePersist);
        $relationMetadata->setOrphanRemoval($attribute->orphanRemoval);
    }
}
