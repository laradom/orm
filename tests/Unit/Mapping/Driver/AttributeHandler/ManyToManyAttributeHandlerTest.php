<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Enum\CascadeType;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ManyToManyAttributeHandler;
use Laradom\ORM\Mapping\RelationMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ManyToManyAttributeHandlerTest extends TestCase
{
    private ManyToManyAttributeHandler $handler;
    private RelationMetadata $relationMetadata;

    protected function setUp(): void
    {
        $this->handler = new ManyToManyAttributeHandler();
        $this->relationMetadata = new RelationMetadata();
    }

    public function testSupport(): void
    {
        $propertyWithAttribute = new ReflectionProperty(EntityWithManyToMany::class, 'roles');
        $propertyWithoutAttribute = new ReflectionProperty(EntityWithManyToMany::class, 'name');

        $this->assertTrue($this->handler->support($propertyWithAttribute));
        $this->assertFalse($this->handler->support($propertyWithoutAttribute));
    }

    public function testHandleWithInversedBy(): void
    {
        $property = new ReflectionProperty(EntityWithManyToMany::class, 'roles');

        $this->handler->handle($property, $this->relationMetadata);

        $this->assertEquals(RelationTypes::ManyToMany, $this->relationMetadata->getType());
        $this->assertEquals('roles', $this->relationMetadata->getFieldName());
        $this->assertEquals('TestRole', $this->relationMetadata->getTargetEntity());
        $this->assertEquals('users', $this->relationMetadata->getInversedBy());
        $this->assertNull($this->relationMetadata->getMappedBy());
        $this->assertTrue($this->relationMetadata->getCascade()->isPersist());
        $this->assertTrue($this->relationMetadata->isOrphanRemoval());
    }

    public function testHandleWithMappedBy(): void
    {
        $property = new ReflectionProperty(EntityWithManyToMany::class, 'users');

        $this->handler->handle($property, $this->relationMetadata);

        $this->assertEquals(RelationTypes::ManyToMany, $this->relationMetadata->getType());
        $this->assertEquals('users', $this->relationMetadata->getFieldName());
        $this->assertEquals('TestUser', $this->relationMetadata->getTargetEntity());
        $this->assertNull($this->relationMetadata->getInversedBy());
        $this->assertEquals('roles', $this->relationMetadata->getMappedBy());
        $this->assertFalse($this->relationMetadata->getCascade()->isPersist());
        $this->assertFalse($this->relationMetadata->isOrphanRemoval());
    }
}

class EntityWithManyToMany
{
    public string $name;

    #[ManyToMany(targetEntity: 'TestRole', inversedBy: 'users', cascade: [CascadeType::PERSIST], orphanRemoval: true)]
    public array $roles;

    #[ManyToMany(targetEntity: 'TestUser', mappedBy: 'roles')]
    public array $users;
}
