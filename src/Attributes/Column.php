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
        public readonly Types $type = Types::STRING,
        public readonly ?int $length = null,
        public readonly bool $nullable = false,
    ) {}
}
