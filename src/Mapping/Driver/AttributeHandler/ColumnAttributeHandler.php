<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionNamedType;
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

        $propertyType = $property->getType();

        $propertyName = $property->getName();

        $namedType = Types::STRING;

        if ($propertyType instanceof ReflectionNamedType) {
            $namedType = Types::from(mb_strtolower($propertyType->getName()));
        }

        $attributeType = $attribute->type ?: $namedType;

        $fieldMetadata->setPropertyName($propertyName);
        $fieldMetadata->setColumnName($attribute->name);
        $fieldMetadata->setType($attributeType);

        $options = $fieldMetadata->getOptions();
        $options->setLength($attribute->length);
        $options->setUnique($attribute->unique);
        $options->setPrecision($attribute->precision);
        $options->setScale($attribute->scale);
        $options->setNullable($attribute->nullable || ($propertyType !== null && $propertyType->allowsNull()));
        $options->setColumnDefinition($attribute->columnDefinition);

        if ($attribute->default !== null) {
            $fieldMetadata->setDefaultValue($attribute->default);
        }
    }
}
