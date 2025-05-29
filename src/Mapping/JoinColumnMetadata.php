<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

class JoinColumnMetadata
{
    public function __construct(
        private string $name,
        private string $referencedColumnName,
        private bool $nullable = false,
        private bool $unique = false,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getReferencedColumnName(): string
    {
        return $this->referencedColumnName;
    }

    public function setReferencedColumnName(string $referencedColumnName): void
    {
        $this->referencedColumnName = $referencedColumnName;
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
}
