<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Driver;

use Laradom\ORM\Mapping\EntityMetadata;

interface DriverInterface
{
    public function extractMetadata(string $className): EntityMetadata;
}
