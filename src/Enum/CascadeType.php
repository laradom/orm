<?php

declare(strict_types=1);

namespace Laradom\ORM\Enum;

enum CascadeType: string
{
    case PERSIST = 'persist';
    case REMOVE = 'remove';
    case REFRESH = 'refresh';
    case MERGE = 'merge';
    case ALL = 'all';
}
