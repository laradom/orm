<?php

declare(strict_types=1);

namespace Laradom\Benchmarks;

use RuntimeException;

class BenchmarkContext
{
    private static array $components = [];

    public static function setComponents(array $components): void
    {
        self::$components = $components;
    }

    public static function getComponent(string $name)
    {
        if (!isset(self::$components[$name])) {
            throw new RuntimeException("The '{$name}' component was not found. Make sure that bootstrap.php it was uploaded.");
        }

        return self::$components[$name];
    }

    public static function getAllComponents(): array
    {
        return self::$components;
    }
}
