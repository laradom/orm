<?php

declare(strict_types=1);

namespace Laradom\ORM\Enum;

enum FetchStrategyMetadata: string
{
    case LAZY = 'lazy';
    case EAGER = 'eager';
    case EXTRA_LAZY = 'extra_lazy';
}
