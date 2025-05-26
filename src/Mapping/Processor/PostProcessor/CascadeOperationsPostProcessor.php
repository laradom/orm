<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\RelationMetadata;

class CascadeOperationsPostProcessor implements EntityMetadataPostProcessorInterface
{
    /**
     * @param EntityMetadata[] $allMetadata
     *
     * @return EntityMetadata[]
     */
    public function process(array $allMetadata): array
    {
        foreach ($allMetadata as $metadata) {
            $this->processCascadeOperations($metadata, $allMetadata);
        }

        return $allMetadata;
    }

    /**
     * @param EntityMetadata[] $allMetadata
     */
    private function processCascadeOperations(EntityMetadata $metadata, array $allMetadata): void
    {
        foreach ($metadata->getRelations() as $relation) {
            $cascade = $relation->getCascade();

            if (!$cascade->isPersist() && !$cascade->isRemove() && !$cascade->isRefresh() && !$cascade->isMerge() && !$cascade->isAll()) {
                $this->setDefaultCascadeOperations($relation);
            }

            $this->ensureConsistentBidirectionalCascade($relation, $allMetadata);
        }
    }

    private function setDefaultCascadeOperations(RelationMetadata $relation): void
    {
        $cascade = $relation->getCascade();

        switch ($relation->getType()) {
            case RelationTypes::OneToOne:
                $cascade->setPersist(true);

                if ($relation->getMappedBy() === null) {
                    $cascade->setRemove(true);
                }
                break;
            case RelationTypes::OneToMany:
                $cascade->setPersist(true);
                $cascade->setMerge(true);
                break;
            case RelationTypes::ManyToOne:
                $cascade->setPersist(true);
                break;
            case RelationTypes::ManyToMany:
                $cascade->setPersist(true);

                if ($relation->getMappedBy() === null) {
                    $cascade->setMerge(true);
                }
                break;
        }
    }

    /**
     * @param EntityMetadata[] $allMetadata
     */
    private function ensureConsistentBidirectionalCascade(
        RelationMetadata $relation,
        array $allMetadata,
    ): void {
        $targetEntity = $relation->getTargetEntity();

        if (!isset($allMetadata[$targetEntity])) {
            return;
        }

        $targetMetadata = $allMetadata[$targetEntity];

        $isBidirectional = false;
        $inverseRelation = null;

        if ($relation->getInversedBy() !== null) {
            $inverseField = $relation->getInversedBy();
            $inverseRelation = $targetMetadata->getRelationByFieldName($inverseField);
            $isBidirectional = $inverseRelation !== null;
        } elseif ($relation->getMappedBy() !== null) {
            $mappedField = $relation->getMappedBy();
            $inverseRelation = $targetMetadata->getRelationByFieldName($mappedField);
            $isBidirectional = $inverseRelation !== null;
        }

        if (!$isBidirectional || $inverseRelation === null) {
            return;
        }

        $this->synchronizeCascadeRemoveOperations($relation, $inverseRelation);
    }

    private function synchronizeCascadeRemoveOperations(
        RelationMetadata $ownerRelation,
        RelationMetadata $inverseRelation,
    ): void {
        $ownerCascade = $ownerRelation->getCascade();
        $inverseCascade = $inverseRelation->getCascade();

        $ownerHasRemove = $ownerCascade->isRemove();

        if (!$ownerHasRemove) {
            return;
        }

        if ($ownerRelation->getType() === RelationTypes::OneToOne || $ownerRelation->getType() === RelationTypes::OneToMany) {
            if ($inverseRelation->getType() === RelationTypes::ManyToOne) {
                if (!$inverseCascade->isRemove()) {
                    $inverseCascade->setRemove(true);
                }
            }
        }
    }
}
