<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\OneToOne;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'user_profiles')]
class UserProfileExample
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private int $id;

    #[Column(type: Types::STRING, length: 255)]
    private string $firstName;

    #[Column(type: Types::STRING, length: 255)]
    private string $lastName;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $phone = null;

    #[OneToOne(targetEntity: UserExample::class, mappedBy: 'profile')]
    private UserExample $user;
}
