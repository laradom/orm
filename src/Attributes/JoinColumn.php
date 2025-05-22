<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class JoinColumn
{
    public function __construct(
        public readonly string $name,
        public readonly string $referencedColumnName = 'id',
        public readonly bool $nullable = false,
        public readonly bool $unique = false,
    ) {}
}
