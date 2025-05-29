<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Mapping\EntityMetadata;

class IndexNamePostProcessor implements EntityMetadataPostProcessorInterface
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

            foreach ($entityMetadata->getIndexes() as $index) {
                if ($index->getName() === null) {
                    $columns = $index->getColumns();
                    $suffix = implode('_', $columns);
                    $prefix = $index->isUnique() ? 'uniq' : 'idx';

                    $indexName = sprintf('%s_%s_%s', $prefix, $tableName, $suffix);
                    $index->setName($indexName);
                }
            }
        }

        return $allMetadata;
    }
}
