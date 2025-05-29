<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;
use Laradom\ORM\Enum\Attributes\Types;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Column
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?Types $type = null,
        public readonly ?int $length = null,
        public readonly bool $nullable = false,
        public readonly bool $unique = false,
        public readonly ?int $precision = null,
        public readonly ?int $scale = null,
        public readonly ?string $columnDefinition = null,
        public readonly mixed $default = null,
    ) {}
}
