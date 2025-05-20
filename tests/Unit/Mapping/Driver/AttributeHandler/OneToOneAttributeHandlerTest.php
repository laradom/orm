<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Mapping\Driver\AttributeHandler\OneToOneAttributeHandler;
use Laradom\ORM\Mapping\RelationMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class OneToOneAttributeHandlerTest extends TestCase
{
    private OneToOneAttributeHandler $handler;
    private RelationMetadata $relationMetadata;

    protected function setUp(): void
    {
        $this->handler = new OneToOneAttributeHandler();
        $this->relationMetadata = new RelationMetadata();
    }

    public function testSupport(): void
    {
        $propertyWithAttribute = new ReflectionProperty(EntityWithOneToOne::class, 'profile');
        $propertyWithoutAttribute = new ReflectionProperty(EntityWithOneToOne::class, 'name');

        $this->assertTrue($this->handler->support($propertyWithAttribute));
        $this->assertFalse($this->handler->support($propertyWithoutAttribute));
    }

    public function testHandle(): void
    {
        $property = new ReflectionProperty(EntityWithOneToOne::class, 'profile');

        $this->handler->handle($property, $this->relationMetadata);

        $this->assertEquals(RelationTypes::OneToOne, $this->relationMetadata->getType());
        $this->assertEquals('profile', $this->relationMetadata->getFieldName());
        $this->assertEquals('TestProfile', $this->relationMetadata->getTargetEntity());
        $this->assertEquals('user', $this->relationMetadata->getInversedBy());
        $this->assertNull($this->relationMetadata->getMappedBy());
        $this->assertTrue($this->relationMetadata->isCascadePersist());
        $this->assertTrue($this->relationMetadata->isOrphanRemoval());
    }

    public function testHandleWithMappedBy(): void
    {
        $property = new ReflectionProperty(EntityWithOneToOne::class, 'userWithMappedBy');

        $this->handler->handle($property, $this->relationMetadata);

        $this->assertEquals(RelationTypes::OneToOne, $this->relationMetadata->getType());
        $this->assertEquals('userWithMappedBy', $this->relationMetadata->getFieldName());
        $this->assertEquals('TestUser', $this->relationMetadata->getTargetEntity());
        $this->assertEquals('profile', $this->relationMetadata->getMappedBy());
        $this->assertNull($this->relationMetadata->getInversedBy());
        $this->assertFalse($this->relationMetadata->isCascadePersist());
        $this->assertFalse($this->relationMetadata->isOrphanRemoval());
    }
}

class EntityWithOneToOne
{
    public string $name;

    #[OneToOne(targetEntity: 'TestProfile', inversedBy: 'user', cascadePersist: true, orphanRemoval: true)]
    public object $profile;

    #[OneToOne(targetEntity: 'TestUser', mappedBy: 'profile')]
    public object $userWithMappedBy;
}
