<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\JoinColumn;
use Laradom\ORM\Mapping\JoinColumnMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

class JoinColumnAttributeHandler extends AbstractAttributeHandler implements RelationHandlerInterface
{
    public function getAttributeClass(): string
    {
        return JoinColumn::class;
    }

    public function support(ReflectionProperty $property): bool
    {
        $attributes = $property->getAttributes(JoinColumn::class);

        return count($attributes) > 0;
    }

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        $attributes = $property->getAttributes(JoinColumn::class);

        foreach ($attributes as $attributeReflection) {
            /** @var JoinColumn $attribute */
            $attribute = $attributeReflection->newInstance();

            $joinColumn = new JoinColumnMetadata(
                name: $attribute->name,
                referencedColumnName: $attribute->referencedColumnName,
                nullable: $attribute->nullable,
                unique: $attribute->unique,
            );

            $relationMetadata->addJoinColumn($joinColumn);
        }
    }
}
