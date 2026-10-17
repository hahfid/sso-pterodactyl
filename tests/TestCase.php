<?php

declare(strict_types=1);

namespace PanelSso\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use PanelSso\SsoServiceProvider;
use Pterodactyl\Models\User;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [SsoServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('remember_token')->nullable();
            $table->boolean('root_admin')->default(false);
            $table->boolean('use_totp')->default(false);
            $table->string('totp_secret')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'username' => 'testuser'.Str::random(4),
            'email' => Str::random(6).'@example.com',
            'password' => 'secret',
            'root_admin' => false,
            'use_totp' => false,
        ], $attributes));
    }
}
