<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class TableNameProcessor implements MetadataProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        if ($entityMetadata->getTableName() === null) {
            $entityMetadata->setTableName($this->namingStrategy->classToTableName($entityMetadata->getClassName()));
        }

        return $entityMetadata;
    }
}
