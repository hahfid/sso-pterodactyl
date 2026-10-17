<?php

declare(strict_types=1);

namespace PanelSso\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use PanelSso\Support\SsoToken;
use Throwable;

class SsoLoginController
{
    public function __construct(private readonly SsoToken $tokens) {}

    public function handle(string $token): RedirectResponse
    {
        $userId = $this->tokens->consume($token);

        if ($userId === null) {
            return redirect()->back()->with('error', 'Token does not exists or has expired');
        }

        try {
            if (! Auth::loginUsingId($userId)) {
                return redirect()->back()->with('error', 'Something went wrong, please try again.');
            }

            session()->regenerate();
        } catch (Throwable) {
            return redirect()->back()->with('error', 'Something went wrong, please try again.');
        }

        return redirect()->intended('/');
    }
}
