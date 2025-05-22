<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\ManyToOne;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\CascadeTypeMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

class ManyToOneAttributeHandler extends AbstractAttributeHandler implements RelationHandlerInterface
{
    public function getAttributeClass(): string
    {
        return ManyToOne::class;
    }

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        /** @var ManyToOne|null $attribute */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $relationMetadata->setType(RelationTypes::ManyToOne);
        $relationMetadata->setFieldName($property->getName());
        $relationMetadata->setTargetEntity($attribute->targetEntity);
        $relationMetadata->setOrphanRemoval($attribute->orphanRemoval);

        if ($attribute->inversedBy !== null) {
            $relationMetadata->setInversedBy($attribute->inversedBy);
        }

        $relationMetadata->setCascade(new CascadeTypeMetadata($attribute->cascade));

        $relationMetadata->setFetchStrategy($attribute->fetch);
    }
}
