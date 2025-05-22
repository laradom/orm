<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;
use Laradom\ORM\Enum\FetchStrategyMetadata;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne
{
    public function __construct(
        public readonly string $targetEntity,
        public readonly ?string $inversedBy = null,
        public readonly array $cascade = [],
        public readonly FetchStrategyMetadata $fetch = FetchStrategyMetadata::LAZY,
        public readonly bool $orphanRemoval = false,
    ) {}
}
