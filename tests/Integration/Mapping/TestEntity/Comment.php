<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping\TestEntity;

use DateTime;
use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\JoinColumn;
use Laradom\ORM\Attributes\ManyToOne;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
class Comment
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::IDENTITY)]
    private int $id;

    #[Column]
    private string $content;

    #[Column(type: Types::DATETIME)]
    private DateTime $createdAt;

    #[ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    private Post $post;
}
