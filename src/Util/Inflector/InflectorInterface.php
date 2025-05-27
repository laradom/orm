<?php

declare(strict_types=1);

namespace Laradom\ORM\Util\Inflector;

interface InflectorInterface
{
    public function singularize(string $plural): string;
    public function pluralize(string $singular): string;
}
