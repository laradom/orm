<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
class UserExample
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    private int $id;
}
