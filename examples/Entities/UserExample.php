<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'user')]
class UserExample
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private int $id;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $email;

    #[OneToMany(targetEntity: PostExample::class, mappedBy: 'user')]
    private array $posts;

    #[OneToOne(targetEntity: UserProfileExample::class, inversedBy: 'user')]
    private ?UserProfileExample $profile = null;

    #[ManyToMany(targetEntity: RoleExample::class, inversedBy: 'users')]
    private array $roles = [];
}
