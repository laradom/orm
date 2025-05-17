<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Naming;

interface NamingStrategyInterface
{
    public function classToTableName(string $className): string;

    public function propertyToColumnName(string $propertyName): string;

    public function referenceColumnName(string $propertyName): string;
}
