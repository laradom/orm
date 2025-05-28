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
                    ($relation->getType() === RelationTypes::ManyToOne
                    || ($relation->getType() === RelationTypes::OneToOne && $relation->getMappedBy() === null))
                    && ($relation->getJoinColumns() === null || count($relation->getJoinColumns()) === 0)
                ) {
                    $targetEntityClass = $relation->getTargetEntity();

                    $targetEntityShortName = $this->namingStrategy->getShortClassName($targetEntityClass);
                    $columnName = $this->namingStrategy->joinColumnName(lcfirst($targetEntityShortName));

                    if (isset($allMetadata[$targetEntityClass])) {
                        $targetTableName = $allMetadata[$targetEntityClass]->getTableName();

                        if ($targetTableName !== null) {
                            $singularName = $this->inflector->singularize($targetTableName);
                            $columnName = $this->namingStrategy->joinColumnName($singularName);
                        }
                    }

                    $joinColumn = new JoinColumnMetadata(
                        name: $columnName,
                        referencedColumnName: $this->namingStrategy->referenceColumnName(),
                        nullable: $relation->getType() !== RelationTypes::OneToOne || $relation->getMappedBy() !== null,
                        unique: $relation->getType() === RelationTypes::OneToOne && $relation->getMappedBy() === null,
                    );

                    $relation->addJoinColumn($joinColumn);
                }
            }
        }

        return $allMetadata;
    }
}
