<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping\TestEntity;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'tags')]
class Tag
{
    #[Id]
    #[Column(name: 'id', type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::IDENTITY)]
    private int $id;

    #[Column(name: 'name', type: Types::STRING, unique: true)]
    private string $name;

    #[ManyToMany(targetEntity: User::class, mappedBy: 'tags')]
    private array $users = [];
}
