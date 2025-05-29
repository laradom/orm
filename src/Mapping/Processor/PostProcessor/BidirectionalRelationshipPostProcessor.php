<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\RelationMetadata;

class BidirectionalRelationshipPostProcessor implements EntityMetadataPostProcessorInterface
{
    /**
     * @param EntityMetadata[] $allMetadata
     *
     * @return EntityMetadata[]
     */
    public function process(array $allMetadata): array
    {
        foreach ($allMetadata as $metadata) {
            foreach ($metadata->getRelations() as $relation) {
                if ($relation->getMappedBy() === null && $relation->getInversedBy() !== null) {
                    $this->setupInverseRelation($allMetadata, $relation);
                }
            }
        }

        return $allMetadata;
    }

    /**
     * @param EntityMetadata[] $allMetadata
     */
    private function setupInverseRelation(array $allMetadata, RelationMetadata $relation): void
    {
        $targetClassName = $relation->getTargetEntity();
        $inversedBy = $relation->getInversedBy();

        if (isset($allMetadata[$targetClassName]) && $inversedBy !== null) {
            $targetMetadata = $allMetadata[$targetClassName];
            $inverseRelation = $targetMetadata->getRelationByFieldName($inversedBy);

            if ($inverseRelation !== null && $inverseRelation->getMappedBy() === null) {
                $inverseRelation->setMappedBy($relation->getFieldName());
            }
        }
    }
}
