<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

final class IdAttributeHandler extends AbstractAttributeHandler implements FieldHandlerInterface
{
    public function getAttributeClass(): string
    {
        return Id::class;
    }

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void
    {
        /**
         * @var Id|null $attribute
         */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $fieldMetadata->setIsPrimaryKey(true);
    }
}
