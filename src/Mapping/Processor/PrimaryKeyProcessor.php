<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\ColumnOptionsMetadata;
use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\GeneratedFieldMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class PrimaryKeyProcessor implements MetadataProcessorInterface
{
    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        if ($entityMetadata->getPrimaryKey() === null) {
            $idField = new FieldMetadata();
            $idField->setPropertyName('id');
            $idField->setColumnName('id');
            $idField->setType(Types::INTEGER);
            $idField->setIsPrimaryKey(true);

            $generatedField = new GeneratedFieldMetadata();
            $generatedField->setIsGenerated(true);
            $generatedField->setGeneratorType(GeneratorType::AUTO);
            $generatedField->setGeneratedCustomClass(null);

            $idField->setGeneratedFieldMetadata($generatedField);

            $options = new ColumnOptionsMetadata();
            $options->setNullable(false);
            $options->setUnique(true);
            $idField->setOptions($options);

            $entityMetadata->addField($idField);
        }

        return $entityMetadata;
    }
}
