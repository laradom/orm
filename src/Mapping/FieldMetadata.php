<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Laradom\ORM\Enum\Attributes\Types;

class FieldMetadata
{
    private string $propertyName;
    private ?string $columnName;
    private Types $type;
    private ?int $length;
    private bool $nullable;
    private bool $isPrimaryKey = false;
    private ?GeneratedFieldMetadata $generatedFieldMetadata = null;

    public function getColumnName(): ?string
    {
        return $this->columnName;
    }

    public function setColumnName(?string $columnName): void
    {
        $this->columnName = $columnName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    public function getType(): Types
    {
        return $this->type;
    }

    public function setType(Types $type): void
    {
        $this->type = $type;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): void
    {
        $this->length = $length;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function setIsPrimaryKey(bool $isPrimaryKey): void
    {
        $this->isPrimaryKey = $isPrimaryKey;
    }

    public function getGeneratedFieldMetadata(): ?GeneratedFieldMetadata
    {
        return $this->generatedFieldMetadata;
    }

    public function setGeneratedFieldMetadata(?GeneratedFieldMetadata $generatedFieldMetadata): void
    {
        $this->generatedFieldMetadata = $generatedFieldMetadata;
    }
}
