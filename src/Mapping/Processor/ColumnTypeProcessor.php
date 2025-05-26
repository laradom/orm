<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class ColumnTypeProcessor implements MetadataProcessorInterface
{
    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        foreach ($entityMetadata->getFields() as $field) {
            $options = $field->getOptions();

            switch ($field->getType()) {
                case Types::STRING:
                    if ($options->getLength() === null) {
                        $options->setLength(255);
                    }
                    break;
                case Types::FLOAT:
                    if ($options->getPrecision() === null) {
                        $options->setPrecision(10);
                        $options->setScale(2);
                    }
                    break;
            }
        }

        return $entityMetadata;
    }
}
