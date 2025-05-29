<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;
use Laradom\ORM\Util\Inflector\InflectorInterface;

class TableNameProcessor implements MetadataProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
        private readonly InflectorInterface $inflector,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        if ($entityMetadata->getTableName() !== null) {
            $name = $entityMetadata->getTableName();
        } else {
            $name = $this->namingStrategy->classToTableName($entityMetadata->getClassName());
            $name = $this->inflector->pluralize($name);
        }

        $entityMetadata->setTableName($name);

        return $entityMetadata;
    }
}
