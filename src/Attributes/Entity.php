<?php

declare(strict_types=1);

namespace Laradom\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Entity
{
    public function __construct() {}
}
