<?php

declare(strict_types=1);

namespace Laradom\ORM\Enum\Attributes;

enum RelationTypes: string
{
    case OneToOne = 'one-to-one';
    case OneToMany = 'one-to-many';
    case ManyToOne = 'many-to-one';
    case ManyToMany = 'many-to-many';
}
