<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Exception\InvalidArgumentException;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\GeneratedFieldMetadata;
use ReflectionProperty;

final class GeneratedValueAttributeHandler extends AbstractPropertyAttributeHandler
{
    public function getAttributeClass(): string
    {
        return GeneratedValue::class;
    }

    public function handle(ReflectionProperty $property, FieldMetadata $fieldMetadata): void
    {
        if (empty($property->getAttributes(Id::class))) {
            throw new InvalidArgumentException('The "GeneratedValue" attribute can only be used on id fields.');
        }

        /**
         * @var GeneratedValue|null $columnAttribute
         */
        $columnAttribute = $this->getPropertyAttribute($property);

        if ($columnAttribute === null) {
            return;
        }

        $generatedFieldMetadata = new GeneratedFieldMetadata();
        $generatedFieldMetadata->setIsGenerated(true);
        $generatedFieldMetadata->setGeneratorType($columnAttribute->strategy);
        $generatedFieldMetadata->setGeneratedCustomClass($columnAttribute->customClass);

        $fieldMetadata->setGeneratedFieldMetadata($generatedFieldMetadata);
    }
}
