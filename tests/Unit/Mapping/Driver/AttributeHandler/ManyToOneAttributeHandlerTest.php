<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Driver\AttributeHandler;

use Laradom\ORM\Attributes\ManyToOne;
use Laradom\ORM\Enum\Attributes\RelationTypes;
use Laradom\ORM\Enum\CascadeType;
use Laradom\ORM\Mapping\Driver\AttributeHandler\ManyToOneAttributeHandler;
use Laradom\ORM\Mapping\RelationMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ManyToOneAttributeHandlerTest extends TestCase
{
    private ManyToOneAttributeHandler $handler;
    private RelationMetadata $relationMetadata;

    protected function setUp(): void
    {
        $this->handler = new ManyToOneAttributeHandler();
        $this->relationMetadata = new RelationMetadata();
    }

    public function testSupport(): void
    {
        $propertyWithAttribute = new ReflectionProperty(EntityWithManyToOne::class, 'user');
        $propertyWithoutAttribute = new ReflectionProperty(EntityWithManyToOne::class, 'title');

        $this->assertTrue($this->handler->support($propertyWithAttribute));
        $this->assertFalse($this->handler->support($propertyWithoutAttribute));
    }

    public function testHandle(): void
    {
        $property = new ReflectionProperty(EntityWithManyToOne::class, 'user');

        $this->handler->handle($property, $this->relationMetadata);

        $this->assertEquals(RelationTypes::ManyToOne, $this->relationMetadata->getType());
        $this->assertEquals('user', $this->relationMetadata->getFieldName());
        $this->assertEquals('TestUser', $this->relationMetadata->getTargetEntity());
        $this->assertEquals('posts', $this->relationMetadata->getInversedBy());
        $this->assertNull($this->relationMetadata->getMappedBy());
        $this->assertTrue($this->relationMetadata->getCascade()->isPersist());
    }
}

class EntityWithManyToOne
{
    public string $title;

    #[ManyToOne(targetEntity: 'TestUser', inversedBy: 'posts', cascade: [CascadeType::PERSIST])]
    public object $user;
}
