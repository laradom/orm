<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Index
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly array $columns = [],
        public readonly bool $unique = false,
    ) {}
}
