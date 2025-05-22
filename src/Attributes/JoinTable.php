<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinTable
{
    public function __construct(
        public readonly string $name,
        public readonly array $joinColumns = [],
        public readonly array $inverseJoinColumns = [],
    ) {}
}
