<?php

declare(strict_types=1);

namespace Pterodactyl\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'root_admin',
        'use_totp',
        'totp_secret',
    ];

    protected $casts = [
        'root_admin' => 'boolean',
        'use_totp' => 'boolean',
    ];
}
