<?php

declare(strict_types=1);

namespace PanelSso\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Pterodactyl\Traits\Commands\EnvironmentWriterTrait;

class GenerateSecretKey extends Command
{
    use EnvironmentWriterTrait;

    protected $description = 'Generate new SSO secret key';

    protected $signature = 'panel-sso:generate';

    public function handle(): int
    {
        $secret = Str::random(48);

        $this->writeToEnvironment(['PANEL_SSO_SECRET' => $secret]);

        $this->info("Generated new secret key: {$secret}");

        return 0;
    }
}
