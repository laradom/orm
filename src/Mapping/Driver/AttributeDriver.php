<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver;

use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Exception\EntityNotFoundException;
use Laradom\ORM\Mapping\EntityMetadata;
use ReflectionClass;
use ReflectionException;

class AttributeDriver implements DriverInterface
{
    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     * @throws EntityNotFoundException
     */
    public function extractMetadata(string $className): EntityMetadata
    {
        $reflectionClass = new ReflectionClass($className);

        if (!$this->supports($reflectionClass)) {
            throw new EntityNotFoundException(sprintf(
                'The "%s" class does not have the "%s" attribute.',
                $className,
                Entity::class,
            ));
        }

        $metadata = new EntityMetadata($className);

        $tableAttributes = $reflectionClass->getAttributes(Table::class);

        if (!empty($tableAttributes)) {
            $tableAttribute = $tableAttributes[0]->newInstance();
            $metadata->setTableName($tableAttribute->name);
        }

        return $metadata;
    }

    /**
     * @param ReflectionClass<object> $classReflection
     */
    private function supports(ReflectionClass $classReflection): bool
    {
        return !empty($classReflection->getAttributes(Entity::class));
    }
}
