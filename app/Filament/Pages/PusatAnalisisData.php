<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PusatAnalisisData extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static \UnitEnum|string|null $navigationGroup = 'Penilaian';
    protected static ?string $title = 'Pusat Analisis Data (AI)';
    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.pusat-analisis-data';

    public static function shouldRegisterNavigation(): bool
    {
        $roleName = auth()->user()?->roles?->first()?->name ?? 'siswa';
        return in_array(strtolower($roleName), ['super_admin', 'admin', 'guru']);
    }
}
