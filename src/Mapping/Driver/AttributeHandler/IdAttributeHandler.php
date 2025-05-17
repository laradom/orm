<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

final class IdAttributeHandler extends AbstractPropertyAttributeHandler
{
    public function getAttributeClass(): string
    {
        return Id::class;
    }

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void
    {
        $columnAttribute = $this->getPropertyAttribute($property);

        if ($columnAttribute === null) {
            return;
        }

        $fieldMetadata->setIsId(true);
    }
}
