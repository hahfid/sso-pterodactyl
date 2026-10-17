<?php

declare(strict_types=1);

namespace PanelSso\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use PanelSso\Support\SsoToken;
use PanelSso\Tests\TestCase;

class SsoTokenTest extends TestCase
{
    public function test_create_returns_random_token_of_configured_length_and_stores_user_id(): void
    {
        $token = (new SsoToken)->create(7);

        $this->assertSame(48, strlen($token));
        $this->assertSame(7, Cache::get('panel-sso:'.$token));
    }

    public function test_create_honors_configured_token_length_and_lifetime(): void
    {
        config()->set('panel-sso.token.length', 16);
        config()->set('panel-sso.token.lifetime', 30);

        $spy = Cache::spy();
        $token = (new SsoToken)->create(9);

        $this->assertSame(16, strlen($token));
        $spy->shouldHaveReceived('put')->once()->with('panel-sso:'.$token, 9, 30);
    }

    public function test_consume_returns_user_id_and_invalidates_the_token(): void
    {
        $tokens = new SsoToken;
        $token = $tokens->create(5);

        $this->assertSame(5, $tokens->consume($token));
        $this->assertNull($tokens->consume($token));
        $this->assertFalse(Cache::has('panel-sso:'.$token));
    }

    public function test_consume_unknown_token_returns_null(): void
    {
        $this->assertNull((new SsoToken)->consume('does-not-exist'));
    }
}
