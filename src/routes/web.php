<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PanelSso\Http\Controllers\SsoLoginController;
use PanelSso\Http\Controllers\SsoWebhookController;

Route::middleware('web')->group(function () {
    Route::get('/panel-sso', [SsoWebhookController::class, 'handle'])->name('panel-sso.webhook');
    Route::get('/panel-sso/{token}', [SsoLoginController::class, 'handle'])->name('panel-sso.login');
});
