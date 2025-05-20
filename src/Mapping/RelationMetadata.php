<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Laradom\ORM\Enum\Attributes\RelationTypes;

class RelationMetadata
{
    private RelationTypes $type;
    private string $fieldName;
    private string $targetEntity;
    private ?string $mappedBy = null;
    private ?string $inversedBy = null;
    private bool $cascadePersist = false;
    private bool $orphanRemoval = false;

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

    public function isCascadePersist(): bool
    {
        return $this->cascadePersist;
    }

    public function setCascadePersist(bool $cascadePersist): void
    {
        $this->cascadePersist = $cascadePersist;
    }

    public function isOrphanRemoval(): bool
    {
        return $this->orphanRemoval;
    }

    public function setOrphanRemoval(bool $orphanRemoval): void
    {
        $this->orphanRemoval = $orphanRemoval;
    }
}
