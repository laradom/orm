<?php

declare(strict_types=1);

namespace Laradom\ORM\Enum\Attributes;

use InvalidArgumentException;

enum Types: string
{
    case INTEGER = 'int';
    case STRING = 'string';
    case BOOLEAN = 'bool';
    case FLOAT = 'float';
    case DATETIME = 'datetime';
    case DATETIME_IMMUTABLE = 'datetime_immutable';
    case ARRAY = 'array';
    case JSON = 'json';

    public static function fromString(string $type): self
    {
        return match (strtolower($type)) {
            'int', 'integer' => self::INTEGER,
            'string', 'text' => self::STRING,
            'bool', 'boolean' => self::BOOLEAN,
            'float', 'double', 'decimal' => self::FLOAT,
            'datetime', 'timestamp' => self::DATETIME,
            'datetime_immutable' => self::DATETIME_IMMUTABLE,
            'array' => self::ARRAY,
            'json' => self::JSON,
            default => throw new InvalidArgumentException("Неизвестный тип колонки: {$type}"),
        };
    }
}
