<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;
use Laradom\ORM\Enum\GeneratorType\GeneratorType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GeneratedValue
{
    public function __construct(
        public readonly ?GeneratorType $strategy = GeneratorType::IDENTITY,
        public readonly ?string $customClass = null,
    ) {}
}
