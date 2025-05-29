<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Mapping\EntityMetadata;

class UniqueConstraintNamePostProcessor implements EntityMetadataPostProcessorInterface
{
    /**
     * @param EntityMetadata[] $allMetadata
     *
     * @return EntityMetadata[]
     */
    public function process(array $allMetadata): array
    {
        foreach ($allMetadata as $entityMetadata) {
            $tableName = $entityMetadata->getTableName();

            foreach ($entityMetadata->getUniqueConstraints() as $constraint) {
                if ($constraint->getName() === null) {
                    $columns = $constraint->getColumns();
                    $suffix = implode('_', $columns);

                    $constraintName = sprintf('uniq_%s_%s', $tableName, $suffix);
                    $constraint->setName($constraintName);
                }
            }
        }

        return $allMetadata;
    }
}
