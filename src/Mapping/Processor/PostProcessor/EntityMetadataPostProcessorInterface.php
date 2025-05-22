<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

interface EntityMetadataPostProcessorInterface
{
    public function process(array $allMetadata): array;
}
