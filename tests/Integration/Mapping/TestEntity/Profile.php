<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping\TestEntity;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
class Profile
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::IDENTITY)]
    private int $id;

    #[Column]
    private string $bio;

    #[Column(nullable: true)]
    private ?string $avatar = null;

    #[OneToOne(targetEntity: User::class, mappedBy: 'profile')]
    private User $user;
}
