<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Enum\CascadeType;
use Laradom\ORM\Mapping\Driver\AttributeHandler\OneToManyAttributeHandler;
use Laradom\ORM\Mapping\RelationMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class OneToManyAttributeHandlerTest extends TestCase
{
    private OneToManyAttributeHandler $handler;
    private RelationMetadata $relationMetadata;

    protected function setUp(): void
    {
        $this->handler = new OneToManyAttributeHandler();
        $this->relationMetadata = new RelationMetadata();
    }

    public function testSupport(): void
    {
        $propertyWithAttribute = new ReflectionProperty(EntityWithOneToMany::class, 'posts');
        $propertyWithoutAttribute = new ReflectionProperty(EntityWithOneToMany::class, 'name');

        $this->assertTrue($this->handler->support($propertyWithAttribute));
        $this->assertFalse($this->handler->support($propertyWithoutAttribute));
    }

    public function testHandle(): void
    {
        $property = new ReflectionProperty(EntityWithOneToMany::class, 'posts');

        $this->handler->handle($property, $this->relationMetadata);

        $this->assertEquals(RelationTypes::OneToMany, $this->relationMetadata->getType());
        $this->assertEquals('posts', $this->relationMetadata->getFieldName());
        $this->assertEquals('TestPost', $this->relationMetadata->getTargetEntity());
        $this->assertEquals('user', $this->relationMetadata->getMappedBy());
        $this->assertNull($this->relationMetadata->getInversedBy());
        $this->assertTrue($this->relationMetadata->getCascade()->isPersist());
        $this->assertTrue($this->relationMetadata->isOrphanRemoval());
    }
}

class EntityWithOneToMany
{
    public string $name;

    #[OneToMany(targetEntity: 'TestPost', mappedBy: 'user', cascade: [CascadeType::PERSIST], orphanRemoval: true)]
    public array $posts;
}
