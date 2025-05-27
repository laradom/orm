<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping\TestEntity;

use DateTime;
use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\JoinColumn;
use Laradom\ORM\Attributes\ManyToMany;
use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Enum\CascadeType;

#[Entity]
#[Table(name: 'users')]
class User
{
    #[Id]
    #[Column(name: 'id', type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::IDENTITY)]
    private int $id;

    #[Column(name: 'email', type: Types::STRING, unique: true)]
    private string $email;

    #[Column(name: 'name', type: Types::STRING)]
    private string $name;

    #[Column(name: 'created_at', type: Types::DATETIME)]
    private DateTime $createdAt;

    #[OneToOne(targetEntity: Profile::class, inversedBy: 'user', cascade: [CascadeType::PERSIST, CascadeType::REMOVE])]
    #[JoinColumn(name: 'profile_id', referencedColumnName: 'id')]
    private Profile $profile;

    #[OneToMany(targetEntity: Post::class, mappedBy: 'user', cascade: [CascadeType::PERSIST], orphanRemoval: true)]
    private array $posts = [];

    #[ManyToMany(targetEntity: Tag::class, inversedBy: 'users', cascade: [CascadeType::PERSIST])]
    private array $tags = [];
}
