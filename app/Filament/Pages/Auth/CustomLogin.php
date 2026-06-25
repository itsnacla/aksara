<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    /**
     * Override the default view.
     */
    protected string $view = 'filament.pages.auth.login';

    /**
     * Override the default layout.
     */
    protected static string $layout = 'filament-panels::components.layout.base';

    public function getTitle(): string|Htmlable
    {
        return 'Masuk ke Portal Akademik';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Masuk ke Akun Anda';
    }
}
