<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use DateTime;
use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\Index;
use Laradom\ORM\Attributes\JoinTable;
use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Attributes\UniqueConstraint;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'users')]
#[Index(name: 'idx_user_created_at', columns: ['created_at'])]
#[Index(name: 'idx_user_name', columns: ['name'])]
#[UniqueConstraint(columns: ['email'])]
class UserExample
{
    #[Id]
    #[Column(name: 'id', type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::AUTO)]
    private int $id;

    #[Column(name: 'name', type: Types::STRING, length: 255)]
    private string $name;

    #[Column(name: 'email', type: Types::STRING, length: 255)]
    private string $email;

    #[Column(name: 'password', type: Types::STRING, length: 255)]
    private string $password;

    #[Column(name: 'created_at', type: Types::DATETIME)]
    private DateTime $createdAt;

    #[OneToMany(targetEntity: PostExample::class, mappedBy: 'user')]
    private array $posts = [];

    #[OneToOne(targetEntity: UserProfileExample::class, inversedBy: 'user')]
    private ?UserProfileExample $profile = null;

    #[ManyToMany(targetEntity: RoleExample::class, inversedBy: 'users')]
    private array $roles = [];
}
