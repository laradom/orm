<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;

class UniqueConstraintNameProcessor implements MetadataProcessorInterface
{
    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
        $tableName = $entityMetadata->getTableName();

        foreach ($entityMetadata->getUniqueConstraints() as $constraint) {
            if ($constraint->getName() === null) {
                $columns = $constraint->getColumns();
                $suffix = implode('_', $columns);

                $constraintName = sprintf('uniq_%s_%s', $tableName, $suffix);
                $constraint->setName($constraintName);
            }
        }

        return $entityMetadata;
    }
}
