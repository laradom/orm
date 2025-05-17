<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;

class MetadataProcessorPipeline
{
    /**
     * @param MetadataProcessorInterface[] $processors
     */
    public function __construct(
        private readonly array $processors,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        foreach ($this->processors as $processor) {
            $entityMetadata = $processor->process($entityMetadata);
        }

        return $entityMetadata;
    }
}
