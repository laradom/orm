<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\JoinColumnMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Util\Inflector\InflectorInterface;

class JoinColumnPostProcessor implements EntityMetadataPostProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
        private readonly InflectorInterface $inflector,
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
                if (
                    ($relation->getType() === RelationTypes::ManyToOne || $relation->getType() === RelationTypes::OneToOne)
                    && ($relation->getJoinColumns() === null || count($relation->getJoinColumns()) === 0)
                ) {
                    $targetEntityClass = $relation->getTargetEntity();

                    if (isset($allMetadata[$targetEntityClass])) {
                        $targetTableName = $allMetadata[$targetEntityClass]->getTableName();
                        $singularName = $this->inflector->singularize($targetTableName);
                        $columnName = $this->namingStrategy->joinColumnName($singularName);
                    } else {
                        $targetEntityShortName = $this->namingStrategy->getShortClassName($targetEntityClass);
                        $columnName = $this->namingStrategy->joinColumnName(lcfirst($targetEntityShortName));
                    }

                    $isUnique = false;

                    if ($relation->getType() === RelationTypes::OneToOne && $relation->getMappedBy() === null) {
                        $isUnique = true;
                    }

                    $joinColumn = new JoinColumnMetadata(
                        name: $columnName,
                        referencedColumnName: $this->namingStrategy->referenceColumnName(),
                        nullable: !($relation->getType() === RelationTypes::OneToOne) || $relation->getMappedBy() !== null,
                        unique: $isUnique,
                    );

                    $relation->addJoinColumn($joinColumn);
                }
            }
        }

        return $allMetadata;
    }
}
