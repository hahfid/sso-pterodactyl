<?php

declare(strict_types=1);

namespace PanelSso\Tests\Feature;

use PanelSso\Support\SsoToken;
use PanelSso\Tests\TestCase;

class SsoLoginTest extends TestCase
{
    public function test_logs_user_in_with_a_valid_token_and_redirects_to_default(): void
    {
        $user = $this->user();
        $token = app(SsoToken::class)->create($user->id);

        $this->get('/panel-sso/'.$token)->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_token_can_only_be_used_once(): void
    {
        $user = $this->user();
        $token = app(SsoToken::class)->create($user->id);

        $this->get('/panel-sso/'.$token)->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->app['auth']->logout();
        $this->flushSession();

        $this->from('/login')->get('/panel-sso/'.$token)
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Token does not exists or has expired');

        $this->assertGuest();
    }

    public function test_redirects_back_with_error_for_unknown_token(): void
    {
        $this->from('/login')->get('/panel-sso/unknown-token')
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Token does not exists or has expired');

        $this->assertGuest();
    }

    public function test_honors_custom_intended_url(): void
    {
        $user = $this->user();
        $token = app(SsoToken::class)->create($user->id);

        $this->withSession(['url.intended' => '/servers'])
            ->get('/panel-sso/'.$token)
            ->assertRedirect('/servers');

        $this->assertAuthenticatedAs($user);
    }

    public function test_shows_generic_error_when_user_vanished_after_token_mint(): void
    {
        $user = $this->user();
        $token = app(SsoToken::class)->create($user->id);

        $user->delete();

        $this->from('/login')->get('/panel-sso/'.$token)
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Something went wrong, please try again.');

        $this->assertGuest();
    }
}
