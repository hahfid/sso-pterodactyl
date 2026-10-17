<?php

declare(strict_types=1);

namespace Pterodactyl\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property bool $root_admin
 * @property bool $use_totp
 * @property string|null $totp_secret
 */
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
