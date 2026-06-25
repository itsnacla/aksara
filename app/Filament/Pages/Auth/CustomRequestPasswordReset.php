<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class CustomRequestPasswordReset extends BaseRequestPasswordReset
{
    /**
     * Override the default view.
     */
    protected string $view = 'filament.pages.auth.request-password-reset';

    /**
     * Override the default layout.
     */
    protected static string $layout = 'filament-panels::components.layout.base';
}
