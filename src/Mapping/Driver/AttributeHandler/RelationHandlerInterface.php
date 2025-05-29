<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Mapping\RelationMetadata;
use ReflectionProperty;

interface RelationHandlerInterface extends AttributeHandlerInterface
{
    public function handle(ReflectionProperty $property, RelationMetadata $relationMetadata): void;
}
