<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

class JoinTableMetadata
{
    /** @var JoinColumnMetadata[] */
    private array $joinColumns = [];

    /** @var JoinColumnMetadata[] */
    private array $inverseJoinColumns = [];

    public function __construct(
        private string $name,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return JoinColumnMetadata[]
     */
    public function getJoinColumns(): array
    {
        return $this->joinColumns;
    }

    public function addJoinColumn(JoinColumnMetadata $joinColumn): void
    {
        $this->joinColumns[] = $joinColumn;
    }

    /**
     * @return JoinColumnMetadata[]
     */
    public function getInverseJoinColumns(): array
    {
        return $this->inverseJoinColumns;
    }

    public function addInverseJoinColumn(JoinColumnMetadata $inverseJoinColumn): void
    {
        $this->inverseJoinColumns[] = $inverseJoinColumn;
    }
}
