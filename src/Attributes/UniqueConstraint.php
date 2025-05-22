<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class UniqueConstraint
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly array $columns = [],
    ) {}
}
