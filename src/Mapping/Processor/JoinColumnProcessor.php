<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\JoinColumnMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class JoinColumnProcessor implements MetadataProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        foreach ($entityMetadata->getRelations() as $relation) {
            if (
                ($relation->getType() === RelationTypes::ManyToOne || $relation->getType() === RelationTypes::OneToOne)
                && ($relation->getJoinColumns() === null || count($relation->getJoinColumns()) === 0)
            ) {
                $targetEntityShortName = $this->getShortClassName($relation->getTargetEntity());
                $columnName = $this->namingStrategy->joinColumnName(lcfirst($targetEntityShortName));

                $joinColumn = new JoinColumnMetadata(
                    name: $columnName,
                    referencedColumnName: $this->namingStrategy->referenceColumnName(),
                    nullable: !($relation->getType() === RelationTypes::OneToOne) || $relation->getMappedBy() !== null,
                    unique: $relation->getType() === RelationTypes::OneToOne && $relation->getMappedBy() === null,
                );

                $relation->addJoinColumn($joinColumn);
            }
        }

        return $entityMetadata;
    }

    private function getShortClassName(string $fullyQualifiedClassName): string
    {
        if (($pos = strrpos($fullyQualifiedClassName, '\\')) !== false) {
            return substr($fullyQualifiedClassName, $pos + 1);
        }

        return $fullyQualifiedClassName;
    }
}
