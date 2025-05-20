<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Exception\InvalidArgumentException;
use Laradom\ORM\Mapping\FieldMetadata;
use Laradom\ORM\Mapping\GeneratedFieldMetadata;
use ReflectionProperty;

final class GeneratedValueAttributeHandler extends AbstractAttributeHandler implements FieldHandlerInterface
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
         * @var GeneratedValue|null $attribute
         */
        $attribute = $this->getPropertyAttribute($property);

        if ($attribute === null) {
            return;
        }

        $generatedFieldMetadata = new GeneratedFieldMetadata();
        $generatedFieldMetadata->setIsGenerated(true);
        $generatedFieldMetadata->setGeneratorType($attribute->strategy);
        $generatedFieldMetadata->setGeneratedCustomClass($attribute->customClass);

        $fieldMetadata->setGeneratedFieldMetadata($generatedFieldMetadata);
    }
}
