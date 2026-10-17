# PanelSSO — Pterodactyl

Let your clients log into their Pterodactyl panel account with a single redirect.

PanelSSO is a Laravel package that is installed inside your **Pterodactyl Panel**. Your client area (billing/management app) calls one endpoint server-to-server with a shared secret, receives a one-time login URL, and sends the client's browser to it — the client lands in the panel already authenticated.

## Requirements

- Pterodactyl Panel v1.15.x
- PHP 8.2 or 8.3

## Installation

```bash
composer require panelsso/pterodactyl
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --tag=panel-sso
```

2. Generate a shared SSO secret (written to the panel's `.env` as `PANEL_SSO_SECRET`):

```bash
php artisan panel-sso:generate
```

The command prints `Generated new secret key: <secret>`.

3. Paste the generated key into the calling application's configuration.

## Usage

From the calling application (server-side), request a one-time login URL:

```php
$response = Http::get('https://panel.example.com/panel-sso', [
    'sso_secret' => config('services.panel_sso.secret'),
    'user_id' => $user->pterodactyl_id,
]);

if (! $response->successful() || ! $response->json('success')) {
    return back()->withErrors(['sso' => $response->json('message') ?? 'Something went wrong.']);
}

return redirect()->away($response->json('redirect'));
```

When the browser follows the returned `redirect` URL, the user is logged into the panel automatically.

## Behavior

The webhook endpoint `GET /panel-sso` responds with JSON (`{"success":true,"redirect":"<url>"}` on success):

| Status | Message | Meaning |
| --- | --- | --- |
| `403` | `Please configure a SSO Secret` | No shared secret has been configured on the panel. |
| `403` | `Please provide valid credentials` | The `sso_secret` value is missing, malformed, or does not match. |
| `404` | `User does not exist` | The `user_id` is missing, malformed, or matches no panel account. |
| `501` | `You cannot automatically login to admin accounts.` | The target account is a root admin. |
| `501` | `Logging into accounts with 2 Factor Authentication enabled is not supported.` | The target account has TOTP enabled. |
| `200` | — | Success; the response contains the one-time `redirect` URL. |

The login endpoint `GET /panel-sso/{token}` consumes the token and redirects the browser to the panel's default (or previously intended) page. If the token is invalid, expired, or already used, the browser is sent back with the flash message `Token does not exists or has expired`. If the account disappears or the login fails, the flash message is `Something went wrong, please try again.`

## Security

- Root admin accounts and accounts with 2 Factor Authentication enabled can never log in via SSO.
- Login tokens are single-use, expire after 60 seconds, and carry ~285 bits of entropy (48 alphanumeric characters).
- The shared secret is compared server-to-server with a timing-safe comparison and is never exposed to the browser.

## License

PanelSSO is open-source software licensed under the [MIT license](LICENSE).
