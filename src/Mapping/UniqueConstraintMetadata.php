<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

class UniqueConstraintMetadata
{
    public function __construct(
        private ?string $name = null,
        private array $columns = [],
    ) {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param string[] $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }
}
