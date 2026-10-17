<?php

declare(strict_types=1);

namespace PanelSso\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PanelSso\Support\SsoToken;
use Pterodactyl\Models\User;

class SsoWebhookController
{
    public function __construct(private readonly SsoToken $tokens) {}

    public function handle(Request $request): JsonResponse
    {
        if (blank(config('panel-sso.secret'))) {
            return $this->failure('Please configure a SSO Secret', 403);
        }

        $given = $request->query('sso_secret');

        if (! is_string($given) || ! hash_equals((string) config('panel-sso.secret'), $given)) {
            return $this->failure('Please provide valid credentials', 403);
        }

        $userId = filter_var($request->query('user_id'), FILTER_VALIDATE_INT);

        if ($userId === false) {
            return $this->failure('User does not exist', 404);
        }

        $user = User::find($userId);

        if ($user === null) {
            return $this->failure('User does not exist', 404);
        }

        if ($user->root_admin) {
            return $this->failure('You cannot automatically login to admin accounts.', 501);
        }

        if ($user->use_totp) {
            return $this->failure('Logging into accounts with 2 Factor Authentication enabled is not supported.', 501);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('panel-sso.login', $this->tokens->create($user->id)),
        ]);
    }

    private function failure(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
