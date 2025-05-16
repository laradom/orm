<?php

declare(strict_types=1);

namespace Laradom\ORM;

use Illuminate\Support\ServiceProvider;

class LaradomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laradom.php' => config_path('laradom.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/laradom.php', 'laradom');
    }
}
