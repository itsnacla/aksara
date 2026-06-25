<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Auth\CustomRequestPasswordReset;
use App\Filament\Pages\Auth\CustomResetPassword;
use App\Http\Middleware\HandleHybridRedirect;
use App\Models\SchoolSetting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->passwordReset(CustomRequestPasswordReset::class, CustomResetPassword::class)
            ->profile()
            ->brandName(fn () => Schema::hasTable('school_settings') ? (SchoolSetting::first()->name ?? 'AKSARA') : 'AKSARA')
            ->brandLogo(fn () => view('filament.logo'))
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->colors([
                'primary' => '#005da7',
            ])
            ->defaultAvatarProvider(CustomAvatarProvider::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationItems([
                NavigationItem::make('Scan Presensi')
                    ->url(fn (): string => route('scan-presensi'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-qr-code')
                    ->sort(1)
                    ->visible(fn (): bool => auth()->user()?->can('ScanAttendance') ?? false),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Master Data'),
                NavigationGroup::make()
                    ->label('Kurikulum & Referensi'),
                NavigationGroup::make()
                    ->label('Jadwal Pelajaran'),
                NavigationGroup::make()
                    ->label('Akademik & KBM'),
                NavigationGroup::make()
                    ->label('Buku Induk & Rapor'),
                NavigationGroup::make()
                    ->label('Pengembangan Diri'),
                NavigationGroup::make()
                    ->label('Sistem & Konfigurasi'),
            ])
            ->userMenuItems([
                Action::make('impersonate')
                    ->label('Login As')
                    ->url(fn (): string => url('/admin/login-as'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->visible(fn (): bool => auth()->user()?->can('Impersonate') ?? false),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                HandleHybridRedirect::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => Blade::render("@vite(['resources/css/app.css', 'resources/css/chatbot.css', 'resources/js/chatbot.js'])"),
            )
            ->renderHook(
                'panels::body.start',
                fn (): string => session()->has('impersonator_id') ? Blade::render('
                    <div style="background-color: #f59e0b; color: white; padding: 12px 32px; display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 500; position: relative; z-index: 9999; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="height: 20px; width: 20px;" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Anda sedang login sebagai <strong>'.auth()->user()->name.'</strong> (Mode Impersonasi).</span>
                        </div>
                        <form action="'.route('impersonate.logout').'" method="POST">
                            '.csrf_field().'
                            <button type="submit" style="background-color: white; color: #d97706; border: none; padding: 6px 16px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                Kembali ke Admin
                            </button>
                        </form>
                    </div>
                ') : '',
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => auth()->check() ? Blade::render('<x-chatbot />') : '',
            );
    }
}
