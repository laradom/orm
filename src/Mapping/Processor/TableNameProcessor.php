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
        $name = $entityMetadata->getClassName();

        if ($entityMetadata->getTableName() !== null) {
            $name = $entityMetadata->getTableName();
        }

        $entityMetadata->setTableName(
            $this->namingStrategy->classToTableName(
                $this->inflector->pluralize($name),
            ),
        );

        return $entityMetadata;
    }
}
