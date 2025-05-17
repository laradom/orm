<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use ReflectionProperty;

abstract class AbstractPropertyAttributeHandler implements AttributeHandlerInterface
{
    public function support(ReflectionProperty $property): bool
    {
        return !empty($property->getAttributes($this->getAttributeClass()));
    }

    protected function getPropertyAttribute(ReflectionProperty $property): ?object
    {
        $attributes = $property->getAttributes($this->getAttributeClass());

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
