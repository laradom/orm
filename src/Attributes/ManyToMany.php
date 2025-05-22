<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;
use Laradom\ORM\Enum\FetchStrategyMetadata;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany
{
    public function __construct(
        public readonly string $targetEntity,
        public readonly ?string $mappedBy = null,
        public readonly ?string $inversedBy = null,
        public readonly array $cascade = [],
        public readonly bool $orphanRemoval = false,
        public readonly FetchStrategyMetadata $fetch = FetchStrategyMetadata::LAZY,
        public readonly ?string $orderBy = null,
    ) {}
}
