<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

class EntityMetadata
{
    public function __construct(
        private string $className,
        private ?string $tableName = null,
    ) {}

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setTableName(?string $tableName): void
    {
        $this->tableName = $tableName;
    }
}
