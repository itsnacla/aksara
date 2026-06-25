<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

class CustomResetPassword extends BaseResetPassword
{
    /**
     * Override the default view.
     */
    protected string $view = 'filament.pages.auth.reset-password';

    /**
     * Override the default layout.
     */
    protected static string $layout = 'filament-panels::components.layout.base';
}
