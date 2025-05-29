<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Enum\FetchStrategyMetadata;

class RelationMetadata
{
    private RelationTypes $type;
    private string $fieldName;
    private string $targetEntity;
    private ?string $mappedBy = null;
    private ?string $inversedBy = null;
    private CascadeTypeMetadata $cascade;
    private bool $orphanRemoval = false;
    /** @var JoinColumnMetadata[] */
    private array $joinColumns = [];
    private ?JoinTableMetadata $joinTable = null;
    private FetchStrategyMetadata $fetchStrategy = FetchStrategyMetadata::LAZY;

    public function __construct()
    {
        $this->cascade = new CascadeTypeMetadata();
    }

    public function getType(): RelationTypes
    {
        return $this->type;
    }

    public function setType(RelationTypes $type): void
    {
        $this->type = $type;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function setTargetEntity(string $targetEntity): void
    {
        $this->targetEntity = $targetEntity;
    }

    public function getMappedBy(): ?string
    {
        return $this->mappedBy;
    }

    public function setMappedBy(?string $mappedBy): void
    {
        $this->mappedBy = $mappedBy;
    }

    public function getInversedBy(): ?string
    {
        return $this->inversedBy;
    }

    public function setInversedBy(?string $inversedBy): void
    {
        $this->inversedBy = $inversedBy;
    }

    public function getCascade(): CascadeTypeMetadata
    {
        return $this->cascade;
    }

    public function setCascade(CascadeTypeMetadata $cascade): void
    {
        $this->cascade = $cascade;
    }

    public function isCascadePersist(): bool
    {
        return $this->cascade->isPersist();
    }

    public function setCascadePersist(bool $cascadePersist): void
    {
        $this->cascade->setPersist($cascadePersist);
    }

    public function isOrphanRemoval(): bool
    {
        return $this->orphanRemoval;
    }

    public function setOrphanRemoval(bool $orphanRemoval): void
    {
        $this->orphanRemoval = $orphanRemoval;
    }

    /**
     * @return JoinColumnMetadata[]|null
     */
    public function getJoinColumns(): ?array
    {
        return $this->joinColumns;
    }

    public function addJoinColumn(JoinColumnMetadata $joinColumn): void
    {
        $this->joinColumns[] = $joinColumn;
    }

    /**
     * @param JoinColumnMetadata[] $joinColumns
     */
    public function setJoinColumns(array $joinColumns = []): void
    {
        $this->joinColumns = $joinColumns;
    }

    public function getJoinTable(): ?JoinTableMetadata
    {
        return $this->joinTable;
    }

    public function setJoinTable(?JoinTableMetadata $joinTable): void
    {
        $this->joinTable = $joinTable;
    }

    public function getFetchStrategy(): FetchStrategyMetadata
    {
        return $this->fetchStrategy;
    }

    public function setFetchStrategy(FetchStrategyMetadata $fetchStrategy): void
    {
        $this->fetchStrategy = $fetchStrategy;
    }

    public function isEager(): bool
    {
        return $this->fetchStrategy === FetchStrategyMetadata::EAGER;
    }
}
