<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'role')]
class RoleExample
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private int $id;

    #[Column(type: Types::STRING, length: 50)]
    private string $name;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $description = null;

    #[ManyToMany(targetEntity: UserExample::class, mappedBy: 'roles')]
    private array $users = [];
}
