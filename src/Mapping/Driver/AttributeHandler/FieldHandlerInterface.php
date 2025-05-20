<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

interface FieldHandlerInterface extends AttributeHandlerInterface
{
    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void;
}
