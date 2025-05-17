<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class FieldNameProcessor implements MetadataProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        foreach ($entityMetadata->getFields() as $field) {
            if ($field->getColumnName() === null) {
                $field->setColumnName($this->namingStrategy->propertyToColumnName($field->getPropertyName()));
            }
        }

        return $entityMetadata;
    }
}
