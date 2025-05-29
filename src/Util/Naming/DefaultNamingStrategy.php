<?php

declare(strict_types=1);

namespace Laradom\ORM\Util\Naming;

class DefaultNamingStrategy implements NamingStrategyInterface
{
    private string $pattern = '/(?<!^)[A-Z]/';

    public function referenceColumnName(): string
    {
        return 'id';
    }

    public function classToTableName(string $className): string
    {
        $parts = explode('\\', $className);

        $snake = (string) preg_replace($this->pattern, '_$0', end($parts));

        return strtolower($snake);
    }

    public function propertyToColumnName(string $propertyName): string
    {
        $snake = (string) preg_replace($this->pattern, '_$0', $propertyName);

        return strtolower($snake);
    }

    public function joinColumnName(string $propertyName): string
    {
        return strtolower($this->propertyToColumnName($propertyName) . '_' . $this->referenceColumnName());
    }

    public function joinTableName(string $sourceEntity, string $targetEntity): string
    {
        return strtolower($this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity));
    }

    public function joinKeyColumnName(string $entityName): string
    {
        return strtolower($this->classToTableName($entityName) . '_' . $this->referenceColumnName());
    }

    public function getShortClassName(string $className): string
    {
        if (($pos = strrpos($className, '\\')) !== false) {
            return substr($className, $pos + 1);
        }

        return $className;
    }
}
