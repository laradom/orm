<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

class ColumnOptionsMetadata
{
    public function __construct(
        private ?int $length = null,
        private bool $nullable = false,
        private bool $unique = false,
        private ?int $precision = null,
        private ?int $scale = null,
        private ?string $columnDefinition = null,
    ) {}

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

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): void
    {
        $this->unique = $unique;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setPrecision(?int $precision): void
    {
        $this->precision = $precision;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(?int $scale): void
    {
        $this->scale = $scale;
    }

    public function getColumnDefinition(): ?string
    {
        return $this->columnDefinition;
    }

    public function setColumnDefinition(?string $columnDefinition): void
    {
        $this->columnDefinition = $columnDefinition;
    }
}
