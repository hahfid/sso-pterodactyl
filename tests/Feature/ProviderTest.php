<?php

declare(strict_types=1);

namespace PanelSso\Tests\Feature;

use Illuminate\Support\Facades\Route;
use PanelSso\SsoServiceProvider;
use PanelSso\Tests\TestCase;

class ProviderTest extends TestCase
{
    public function test_provider_registers_config_routes_and_middleware(): void
    {
        $this->assertArrayHasKey(SsoServiceProvider::class, $this->app->getLoadedProviders());

        $this->assertSame(48, config('panel-sso.token.length'));
        $this->assertSame(60, config('panel-sso.token.lifetime'));
        $this->assertNull(config('panel-sso.secret'));

        $this->assertTrue(Route::has('panel-sso.webhook'));
        $this->assertTrue(Route::has('panel-sso.login'));
        $this->assertSame(url('/panel-sso'), route('panel-sso.webhook'));
        $this->assertSame(url('/panel-sso/abc'), route('panel-sso.login', 'abc'));

        $this->assertSame(['web'], Route::getRoutes()->getByName('panel-sso.webhook')->middleware());
        $this->assertSame(['web'], Route::getRoutes()->getByName('panel-sso.login')->middleware());
    }

    public function test_config_can_be_published_with_panel_sso_tag(): void
    {
        $target = config_path('panel-sso.php');

        $this->artisan('vendor:publish', ['--tag' => 'panel-sso', '--force' => true])->assertSuccessful();

        $this->assertFileExists($target);
        $published = require $target;
        $this->assertSame(48, $published['token']['length']);
        $this->assertSame(60, $published['token']['lifetime']);

        unlink($target);
    }
}
