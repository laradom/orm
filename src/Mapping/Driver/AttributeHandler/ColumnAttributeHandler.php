<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

final class ColumnAttributeHandler extends AbstractPropertyAttributeHandler
{
    public function getAttributeClass(): string
    {
        return Column::class;
    }

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void
    {
        /**
         * @var Column|null $columnAttribute
         */
        $columnAttribute = $this->getPropertyAttribute($property);

        if ($columnAttribute === null) {
            return;
        }

        $propertyName = $property->getName();

        $fieldMetadata->setPropertyName($propertyName);
        $fieldMetadata->setColumnName($columnAttribute->name);
        $fieldMetadata->setType($columnAttribute->type);
        $fieldMetadata->setLength($columnAttribute->length);
        $fieldMetadata->setNullable($columnAttribute->nullable);
    }
}
