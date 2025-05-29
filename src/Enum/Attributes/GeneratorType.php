<?php

declare(strict_types=1);

namespace Laradom\ORM\Enum\Attributes;

enum GeneratorType: string
{
    case AUTO = 'auto';
    case IDENTITY = 'identity';
    case SEQUENCE = 'sequence';
    case UUID = 'uuid';
    case CUSTOM = 'custom';
}
