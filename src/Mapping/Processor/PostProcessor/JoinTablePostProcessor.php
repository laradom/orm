<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\JoinColumnMetadata;
use Laradom\ORM\Mapping\JoinTableMetadata;
use Laradom\ORM\Util\Inflector\InflectorStrategyInterface;
use Laradom\ORM\Util\Naming\NamingStrategyInterface;

class JoinTablePostProcessor implements EntityMetadataPostProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
        private readonly InflectorStrategyInterface $inflector,
    ) {}

    /**
     * @param EntityMetadata[] $allMetadata
     *
     * @return EntityMetadata[]
     */
    public function process(array $allMetadata): array
    {
        foreach ($allMetadata as $entityMetadata) {
            foreach ($entityMetadata->getRelations() as $relation) {
                if ($relation->getType() === RelationTypes::ManyToMany && $relation->getJoinTable() === null) {
                    $sourceTableName = $entityMetadata->getTableName();
                    $targetEntityClass = $relation->getTargetEntity();

                    if (isset($allMetadata[$targetEntityClass])) {
                        $targetTableName = $allMetadata[$targetEntityClass]->getTableName();
                    } else {
                        $targetTableName = $this->namingStrategy->classToTableName($targetEntityClass);
                    }

                    if ($sourceTableName === null || $targetTableName === null) {
                        continue;
                    }

                    $tableName = $this->namingStrategy->joinTableName($sourceTableName, $targetTableName);

                    $joinTable = new JoinTableMetadata(name: $tableName);

                    $singularSourceName = $this->inflector->singularize($sourceTableName);
                    $joinColumn = new JoinColumnMetadata(
                        name: $this->namingStrategy->joinKeyColumnName($singularSourceName),
                        referencedColumnName: $this->namingStrategy->referenceColumnName(),
                        nullable: false,
                        unique: false,
                    );

                    $joinTable->addJoinColumn($joinColumn);

                    $singularTargetName = $this->inflector->singularize($targetTableName);
                    $inverseJoinColumn = new JoinColumnMetadata(
                        name: $this->namingStrategy->joinKeyColumnName($singularTargetName),
                        referencedColumnName: $this->namingStrategy->referenceColumnName(),
                        nullable: false,
                        unique: false,
                    );

                    $joinTable->addInverseJoinColumn($inverseJoinColumn);

                    $relation->setJoinTable($joinTable);
                }
            }
        }

        return $allMetadata;
    }
}
