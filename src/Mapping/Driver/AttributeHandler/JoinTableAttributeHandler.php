<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\JoinTable;
use Laradom\ORM\Mapping\JoinColumnMetadata;
use Laradom\ORM\Mapping\JoinTableMetadata;
use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

class JoinTableAttributeHandler extends AbstractAttributeHandler implements RelationHandlerInterface
{
    public function getAttributeClass(): string
    {
        return JoinTable::class;
    }

    public function support(ReflectionProperty $property): bool
    {
        $attributes = $property->getAttributes(JoinTable::class);

        return count($attributes) > 0;
    }

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        /** @var JoinTable|null $attribute */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $joinTable = new JoinTableMetadata(name: $attribute->name);

        foreach ($attribute->joinColumns as $joinColumnData) {
            if (isset($joinColumnData['name'])) {
                $joinColumn = new JoinColumnMetadata(
                    name: $joinColumnData['name'],
                    referencedColumnName: $joinColumnData['referencedColumnName'] ?? 'id',
                    nullable: $joinColumnData['nullable'] ?? false,
                    unique: $joinColumnData['unique'] ?? false,
                );
                $joinTable->addJoinColumn($joinColumn);
            }
        }

        foreach ($attribute->inverseJoinColumns as $inverseJoinColumnData) {
            if (isset($inverseJoinColumnData['name'])) {
                $inverseJoinColumn = new JoinColumnMetadata(
                    name: $inverseJoinColumnData['name'],
                    referencedColumnName: $inverseJoinColumnData['referencedColumnName'] ?? 'id',
                    nullable: $inverseJoinColumnData['nullable'] ?? false,
                    unique: $inverseJoinColumnData['unique'] ?? false,
                );
                $joinTable->addInverseJoinColumn($inverseJoinColumn);
            }
        }

        $relationMetadata->setJoinTable($joinTable);
    }
}
