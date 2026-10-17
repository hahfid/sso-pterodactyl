<?php

declare(strict_types=1);

namespace PanelSso\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use PanelSso\Tests\TestCase;

class SsoWebhookTest extends TestCase
{
    private const SECRET = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('panel-sso.secret', self::SECRET);
    }

    public function test_rejects_when_no_secret_is_configured(): void
    {
        config()->set('panel-sso.secret', null);

        $this->get('/panel-sso?sso_secret=whatever&user_id=1')
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Please configure a SSO Secret']);
    }

    public function test_treats_empty_configured_secret_as_missing(): void
    {
        config()->set('panel-sso.secret', '');

        $this->get('/panel-sso?sso_secret=whatever&user_id=1')
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Please configure a SSO Secret']);
    }

    public function test_rejects_wrong_secret(): void
    {
        $user = $this->user();

        $this->get('/panel-sso?sso_secret=wrong&user_id='.$user->id)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Please provide valid credentials']);
    }

    public function test_rejects_non_string_secret_without_crashing(): void
    {
        $user = $this->user();

        $this->get('/panel-sso?sso_secret[]=x&user_id='.$user->id)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Please provide valid credentials']);
    }

    public function test_rejects_missing_secret(): void
    {
        $user = $this->user();

        $this->get('/panel-sso?user_id='.$user->id)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Please provide valid credentials']);
    }

    public function test_rejects_missing_user_id(): void
    {
        $this->get('/panel-sso?sso_secret='.self::SECRET)
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'User does not exist']);
    }

    public function test_rejects_unknown_user(): void
    {
        $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id=999999')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'User does not exist']);
    }

    public function test_rejects_non_integer_like_user_id(): void
    {
        $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id=1.5')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'User does not exist']);
    }

    public function test_rejects_array_user_id(): void
    {
        $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id[]=1')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'User does not exist']);
    }

    public function test_rejects_root_admin(): void
    {
        $user = $this->user(['root_admin' => true]);

        $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id='.$user->id)
            ->assertStatus(501)
            ->assertJson(['success' => false, 'message' => 'You cannot automatically login to admin accounts.']);
    }

    public function test_rejects_totp_enabled_user(): void
    {
        $user = $this->user(['use_totp' => true]);

        $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id='.$user->id)
            ->assertStatus(501)
            ->assertJson([
                'success' => false,
                'message' => 'Logging into accounts with 2 Factor Authentication enabled is not supported.',
            ]);
    }

    public function test_allows_user_with_stored_totp_secret_but_2fa_disabled(): void
    {
        $user = $this->user(['use_totp' => false, 'totp_secret' => 'JBSWY3DPEHPK3PXP']);

        $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id='.$user->id)
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_mints_one_time_token_and_returns_redirect_url(): void
    {
        $user = $this->user();

        $response = $this->get('/panel-sso?sso_secret='.self::SECRET.'&user_id='.$user->id);

        $response->assertOk()->assertJson(['success' => true]);

        $redirect = $response->json('redirect');
        $this->assertIsString($redirect);
        $this->assertStringStartsWith(url('/panel-sso').'/', $redirect);

        $token = substr($redirect, strlen(url('/panel-sso').'/'));
        $this->assertSame(48, strlen($token));
        $this->assertSame($user->id, Cache::get('panel-sso:'.$token));
    }
}
