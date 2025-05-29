<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\ManyToOne;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'posts')]
class PostExample
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private int $id;

    #[Column(type: Types::STRING, length: 255)]
    private string $title;

    #[Column(type: Types::STRING)]
    private string $content;

    #[ManyToOne(targetEntity: UserExample::class, inversedBy: 'posts')]
    private UserExample $user;
}
