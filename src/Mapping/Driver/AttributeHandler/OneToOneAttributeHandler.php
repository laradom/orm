<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\CascadeTypeMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

class OneToOneAttributeHandler extends AbstractAttributeHandler implements RelationHandlerInterface
{
    public function getAttributeClass(): string
    {
        return OneToOne::class;
    }

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        /** @var OneToOne|null $attribute */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $relationMetadata->setType(RelationTypes::OneToOne);
        $relationMetadata->setFieldName($property->getName());
        $relationMetadata->setTargetEntity($attribute->targetEntity);
        $relationMetadata->setOrphanRemoval($attribute->orphanRemoval);

        if ($attribute->mappedBy !== null) {
            $relationMetadata->setMappedBy($attribute->mappedBy);
        }

        if ($attribute->inversedBy !== null) {
            $relationMetadata->setInversedBy($attribute->inversedBy);
        }

        $relationMetadata->setCascade(new CascadeTypeMetadata($attribute->cascade));

        $relationMetadata->setFetchStrategy($attribute->fetch);
    }
}
