<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Laradom\ORM\Enum\CascadeType;

class CascadeTypeMetadata
{
    private bool $persist = false;
    private bool $remove = false;
    private bool $refresh = false;
    private bool $merge = false;
    private bool $all = false;

    /**
     * @param CascadeType[] $cascadeTypes
     */
    public function __construct(array $cascadeTypes = [])
    {
        foreach ($cascadeTypes as $cascadeType) {
            match ($cascadeType) {
                CascadeType::PERSIST => $this->persist = true,
                CascadeType::REMOVE => $this->remove = true,
                CascadeType::REFRESH => $this->refresh = true,
                CascadeType::MERGE => $this->merge = true,
                CascadeType::ALL => $this->all = true,
            };
        }
    }

    public function setPersist(bool $persist): void
    {
        $this->persist = $persist;
    }

    public function isPersist(): bool
    {
        return $this->persist || $this->all;
    }

    public function setRemove(bool $remove): void
    {
        $this->remove = $remove;
    }

    public function isRemove(): bool
    {
        return $this->remove || $this->all;
    }

    public function setRefresh(bool $refresh): void
    {
        $this->refresh = $refresh;
    }

    public function isRefresh(): bool
    {
        return $this->refresh || $this->all;
    }

    public function setMerge(bool $merge): void
    {
        $this->merge = $merge;
    }

    public function isMerge(): bool
    {
        return $this->merge || $this->all;
    }

    public function setAll(bool $all): void
    {
        $this->all = $all;
    }

    public function isAll(): bool
    {
        return $this->all;
    }
}