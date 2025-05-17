<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Naming;

class DefaultNamingStrategy implements NamingStrategyInterface
{
    private string $pattern = '/(?<!^)[A-Z]/';

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

    public function referenceColumnName(string $propertyName): string
    {
        $snake = (string) preg_replace($this->pattern, '_$0', $propertyName);

        return strtolower($snake) . '_id';
    }
}
