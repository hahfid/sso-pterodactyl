<?php

declare(strict_types=1);

namespace PanelSso;

use Illuminate\Support\ServiceProvider;

class SsoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/panel-sso.php' => config_path('panel-sso.php'),
        ], 'panel-sso');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/panel-sso.php', 'panel-sso');
    }
}
