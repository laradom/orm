<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping;

use Laradom\ORM\Enum\GeneratorType\GeneratorType;

class GeneratedFieldMetadata
{
    private bool $isGenerated = false;
    private ?GeneratorType $generatorType = null;
    private ?string $generatedCustomClass = null;

    public function isGenerated(): bool
    {
        return $this->isGenerated;
    }

    public function setIsGenerated(bool $isGenerated): void
    {
        $this->isGenerated = $isGenerated;
    }

    public function getGeneratorType(): ?GeneratorType
    {
        return $this->generatorType;
    }

    public function setGeneratorType(?GeneratorType $generatorType): void
    {
        $this->generatorType = $generatorType;
    }

    public function getGeneratedCustomClass(): ?string
    {
        return $this->generatedCustomClass;
    }

    public function setGeneratedCustomClass(?string $generatedCustomClass): void
    {
        $this->generatedCustomClass = $generatedCustomClass;
    }
}
