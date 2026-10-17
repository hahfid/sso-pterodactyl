<?php

declare(strict_types=1);

namespace Pterodactyl\Traits\Commands;

class EnvironmentWriterSpy
{
    /** @var array<string, mixed> */
    public static array $written = [];

    public static function reset(): void
    {
        static::$written = [];
    }
}
