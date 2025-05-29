<?php

declare(strict_types=1);

namespace Laradom\ORM\Util\Naming;

interface NamingStrategyInterface
{
    public function classToTableName(string $className): string;

    public function propertyToColumnName(string $propertyName): string;

    public function referenceColumnName(): string;

    public function joinColumnName(string $propertyName): string;

    public function joinTableName(string $sourceEntity, string $targetEntity): string;

    public function joinKeyColumnName(string $entityName): string;

    public function getShortClassName(string $className): string;
}
