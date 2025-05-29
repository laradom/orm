<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use ReflectionProperty;

interface AttributeHandlerInterface
{
    public function getAttributeClass(): string;

    public function support(ReflectionProperty $property): bool;
}
