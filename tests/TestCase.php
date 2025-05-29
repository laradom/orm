<?php

declare(strict_types=1);

namespace Laradom\Tests;

use Illuminate\Foundation\Application;
use Laradom\ORM\LaradomServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);
    }

    /**
     * @param Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaradomServiceProvider::class,
        ];
    }
}
