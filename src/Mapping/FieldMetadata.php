<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Laradom\ORM\Enum\Attributes\Types;

class FieldMetadata
{
    private string $propertyName;
    private ?string $columnName;
    private Types $type;
    private bool $isPrimaryKey = false;
    private ?GeneratedFieldMetadata $generatedFieldMetadata = null;
    private mixed $defaultValue = null;
    private bool $hasDefaultValue = false;
    private ColumnOptionsMetadata $options;

    public function __construct()
    {
        $this->options = new ColumnOptionsMetadata();
    }

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

    public function getOptions(): ColumnOptionsMetadata
    {
        return $this->options;
    }

    public function setOptions(ColumnOptionsMetadata $options): void
    {
        $this->options = $options;
    }

    public function getLength(): ?int
    {
        return $this->options->getLength();
    }

    public function setLength(?int $length): void
    {
        $this->options->setLength($length);
    }

    public function isNullable(): bool
    {
        return $this->options->isNullable();
    }

    public function setNullable(bool $nullable): void
    {
        $this->options->setNullable($nullable);
    }

    public function isUnique(): bool
    {
        return $this->options->isUnique();
    }

    public function setUnique(bool $unique): void
    {
        $this->options->setUnique($unique);
    }

    public function getPrecision(): ?int
    {
        return $this->options->getPrecision();
    }

    public function setPrecision(?int $precision): void
    {
        $this->options->setPrecision($precision);
    }

    public function getScale(): ?int
    {
        return $this->options->getScale();
    }

    public function setScale(?int $scale): void
    {
        $this->options->setScale($scale);
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

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(mixed $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
        $this->hasDefaultValue = true;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }
}
