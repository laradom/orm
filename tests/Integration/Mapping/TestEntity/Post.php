<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping\TestEntity;

use DateTime;
use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\ManyToOne;
use Laradom\ORM\Attributes\OneToMany;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Enum\CascadeType;

#[Entity]
class Post
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::IDENTITY)]
    private int $id;

    #[Column]
    private string $title;

    #[Column]
    private string $content;

    #[Column(type: Types::DATETIME)]
    private DateTime $createdAt;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    private User $user;

    #[OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: [CascadeType::PERSIST, CascadeType::REMOVE], orphanRemoval: true)]
    private array $comments = [];
}
