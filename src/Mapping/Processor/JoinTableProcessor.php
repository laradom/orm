<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\JoinColumnMetadata;
use Laradom\ORM\Mapping\JoinTableMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class JoinTableProcessor implements MetadataProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        foreach ($entityMetadata->getRelations() as $relation) {
            if ($relation->getType() === RelationTypes::ManyToMany && $relation->getJoinTable() === null) {
                $sourceEntityName = $this->namingStrategy->getShortClassName($entityMetadata->getClassName());
                $targetEntityName = $this->namingStrategy->getShortClassName($relation->getTargetEntity());

                $tableName = $this->namingStrategy->joinTableName(
                    lcfirst($sourceEntityName),
                    lcfirst($targetEntityName),
                );

                $joinTable = new JoinTableMetadata(name: $tableName);

                $joinColumn = new JoinColumnMetadata(
                    name: $this->namingStrategy->joinKeyColumnName(lcfirst($sourceEntityName)),
                    referencedColumnName: $this->namingStrategy->referenceColumnName(),
                    nullable: false,
                    unique: false,
                );
                $joinTable->addJoinColumn($joinColumn);

                $inverseJoinColumn = new JoinColumnMetadata(
                    name: $this->namingStrategy->joinKeyColumnName(lcfirst($targetEntityName)),
                    referencedColumnName: $this->namingStrategy->referenceColumnName(),
                    nullable: false,
                    unique: false,
                );
                $joinTable->addInverseJoinColumn($inverseJoinColumn);

                $relation->setJoinTable($joinTable);
            }
        }

        return $entityMetadata;
    }
}
