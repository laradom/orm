<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver;

use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessor;
use Laradom\ORM\Mapping\EntityMetadata;
use ReflectionClass;
use ReflectionException;

class AttributeDriver implements DriverInterface
{
    public function __construct(
        private readonly MetadataProcessor $metadataProcessor,
    ) {}

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     * @throws EntityNotFoundException
     */
    public function extractMetadata(string $className): EntityMetadata
    {
        $reflectionClass = new ReflectionClass($className);

        if (!$this->supports($className)) {
            throw new EntityNotFoundException(
                sprintf('The "%s" class does not have the "%s" attribute.', $className, Entity::class),
            );
        }

        $metadata = new EntityMetadata($className);

        $this->metadataProcessor->process($reflectionClass, $metadata);

        return $metadata;
    }

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     */
    public function supports(string $className): bool
    {
        $reflectionClass = new ReflectionClass($className);

        return !empty($reflectionClass->getAttributes(Entity::class));
    }
}
