<?php

declare(strict_types=1);

namespace PanelSso\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SsoToken
{
    public function create(int $userId): string
    {
        $token = Str::random((int) config('panel-sso.token.length', 48));

        Cache::put(
            $this->key($token),
            $userId,
            (int) config('panel-sso.token.lifetime', 60),
        );

        return $token;
    }

    public function consume(string $token): ?int
    {
        $userId = Cache::pull($this->key($token));

        return $userId === null ? null : (int) $userId;
    }

    private function key(string $token): string
    {
        return 'panel-sso:'.$token;
    }
}
