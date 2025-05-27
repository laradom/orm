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

    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void
    {
        /** @var JoinTable|null $attribute */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $joinTable = new JoinTableMetadata(name: $attribute->name);

        foreach ($attribute->joinColumns as $joinColumnData) {
            $joinColumn = new JoinColumnMetadata(
                name: $joinColumnData->name,
                referencedColumnName: $joinColumnData->referencedColumnName,
                nullable: $joinColumnData->nullable,
                unique: $joinColumnData->unique,
            );
            $joinTable->addJoinColumn($joinColumn);
        }

        foreach ($attribute->inverseJoinColumns as $inverseJoinColumnData) {
            $inverseJoinColumn = new JoinColumnMetadata(
                name: $inverseJoinColumnData->name,
                referencedColumnName: $inverseJoinColumnData->referencedColumnName,
                nullable: $inverseJoinColumnData->nullable,
                unique: $inverseJoinColumnData->unique,
            );
            $joinTable->addInverseJoinColumn($inverseJoinColumn);
        }

        $relationMetadata->setJoinTable($joinTable);
    }
}
