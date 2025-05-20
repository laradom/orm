<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

final class ColumnAttributeHandler extends AbstractAttributeHandler implements FieldHandlerInterface
{
    public function getAttributeClass(): string
    {
        return Column::class;
    }

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void
    {
        /**
         * @var Column|null $attribute
         */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $propertyName = $property->getName();

        $fieldMetadata->setPropertyName($propertyName);
        $fieldMetadata->setColumnName($attribute->name);
        $fieldMetadata->setType($attribute->type);
        $fieldMetadata->setLength($attribute->length);
        $fieldMetadata->setNullable($attribute->nullable);
    }
}
