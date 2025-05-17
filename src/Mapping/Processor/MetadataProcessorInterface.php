<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;

interface MetadataProcessorInterface
{
    public function process(EntityMetadata $entityMetadata): EntityMetadata;
}
