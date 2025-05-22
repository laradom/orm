<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor;

use Laradom\ORM\Mapping\EntityMetadata;
use Laradom\ORM\Mapping\Naming\NamingStrategyInterface;

class IndexNameProcessor implements MetadataProcessorInterface
{
    public function __construct(
        private readonly NamingStrategyInterface $namingStrategy,
    ) {}

    public function process(EntityMetadata $entityMetadata): EntityMetadata
    {
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

        return $entityMetadata;
    }
}
