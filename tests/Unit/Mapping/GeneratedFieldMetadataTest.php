<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping;

use Laradom\ORM\Enum\GeneratorType\GeneratorType;
use Laradom\ORM\Mapping\GeneratedFieldMetadata;
use PHPUnit\Framework\TestCase;

class GeneratedFieldMetadataTest extends TestCase
{
    private GeneratedFieldMetadata $metadata;

    protected function setUp(): void
    {
        $this->metadata = new GeneratedFieldMetadata();
    }

    public function testDefaultValues(): void
    {
        $this->assertFalse($this->metadata->isGenerated());
        $this->assertNull($this->metadata->getGeneratorType());
        $this->assertNull($this->metadata->getGeneratedCustomClass());
    }

    public function testSetAndGetIsGenerated(): void
    {
        $this->metadata->setIsGenerated(true);
        $this->assertTrue($this->metadata->isGenerated());

        $this->metadata->setIsGenerated(false);
        $this->assertFalse($this->metadata->isGenerated());
    }

    public function testSetAndGetGeneratorType(): void
    {
        $this->metadata->setGeneratorType(GeneratorType::IDENTITY);
        $this->assertEquals(GeneratorType::IDENTITY, $this->metadata->getGeneratorType());

        $this->metadata->setGeneratorType(GeneratorType::SEQUENCE);
        $this->assertEquals(GeneratorType::SEQUENCE, $this->metadata->getGeneratorType());

        $this->metadata->setGeneratorType(GeneratorType::UUID);
        $this->assertEquals(GeneratorType::UUID, $this->metadata->getGeneratorType());

        $this->metadata->setGeneratorType(GeneratorType::CUSTOM);
        $this->assertEquals(GeneratorType::CUSTOM, $this->metadata->getGeneratorType());

        $this->metadata->setGeneratorType(null);
        $this->assertNull($this->metadata->getGeneratorType());
    }

    public function testSetAndGetGeneratedCustomClass(): void
    {
        $customClass = 'App\CustomGenerator';
        $this->metadata->setGeneratedCustomClass($customClass);
        $this->assertEquals($customClass, $this->metadata->getGeneratedCustomClass());

        $this->metadata->setGeneratedCustomClass(null);
        $this->assertNull($this->metadata->getGeneratedCustomClass());
    }
}
