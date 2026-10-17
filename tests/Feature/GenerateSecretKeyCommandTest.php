<?php

declare(strict_types=1);

namespace PanelSso\Tests\Feature;

use PanelSso\Tests\TestCase;
use Pterodactyl\Traits\Commands\EnvironmentWriterSpy;

class GenerateSecretKeyCommandTest extends TestCase
{
    public function test_generates_and_stores_panel_sso_secret(): void
    {
        EnvironmentWriterSpy::reset();

        $this->artisan('panel-sso:generate')
            ->expectsOutputToContain('Generated new secret key: ')
            ->assertExitCode(0);

        $secret = EnvironmentWriterSpy::$written['PANEL_SSO_SECRET'] ?? null;

        $this->assertIsString($secret);
        $this->assertSame(48, strlen($secret));
    }
}
