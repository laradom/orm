<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

interface AttributeHandlerInterface
{
    public function getAttributeClass(): string;

    public function support(ReflectionProperty $property): bool;

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void;
}
