<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Processor\PostProcessor\EntityMetadataPostProcessorInterface;

class MetadataProcessorPipeline
{
    /**
     * @param MetadataProcessorInterface[] $processors
     * @param EntityMetadataPostProcessorInterface[] $postProcessors
     */
    public function __construct(
        private readonly array $processors,
        private readonly array $postProcessors = [],
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        foreach ($this->processors as $processor) {
            $entityMetadata = $processor->process($entityMetadata);
        }

        return $entityMetadata;
    }

    /**
     * @param EntityMetadata[] $allMetadata
     *
     * @return EntityMetadata[]
     */
    public function postProcess(array $allMetadata): array
    {
        foreach ($this->postProcessors as $postProcessor) {
            $allMetadata = $postProcessor->process($allMetadata);
        }

        return $allMetadata;
    }
}
