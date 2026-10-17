<?php

declare(strict_types=1);

namespace Pterodactyl\Traits\Commands;

trait EnvironmentWriterTrait
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function writeToEnvironment(array $values = []): void
    {
        EnvironmentWriterSpy::$written = $values;
    }
}
