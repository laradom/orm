<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping\TestEntity;

use DateTime;
use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
class User
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    private int $id;

    #[Column(unique: true)]
    private string $email;

    #[Column(default: 'user_name')]
    private string $name;

    #[Column(type: Types::DATETIME)]
    private DateTime $createdAt;

    #[OneToOne(targetEntity: Profile::class, inversedBy: 'user')]
    private Profile $profile;

    #[OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    private array $posts = [];

    #[ManyToMany(targetEntity: Tag::class, inversedBy: 'users')]
    private array $tags = [];
}
