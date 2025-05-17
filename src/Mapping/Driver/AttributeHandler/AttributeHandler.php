<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Mapping\FieldMetadata;
use ReflectionProperty;

class AttributeHandler
{
    /**
     * @param AttributeHandlerInterface[] $handlers
     */
    public function __construct(
        private readonly array $handlers,
    ) {}

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void
    {
        foreach ($this->handlers as $handler) {
            if (!$handler->support($property)) {
                continue;
            }

            $handler->handle($property, $fieldMetadata);
        }
    }
}
