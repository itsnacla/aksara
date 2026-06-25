<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DataProgressStatsWidget;
use App\Filament\Widgets\DataProgressTableWidget;
use Filament\Pages\Page;

class DataProgressTracker extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static \UnitEnum|string|null $navigationGroup = 'Akademik & KBM';

    protected static ?string $navigationLabel = 'Tracking Progress';

    protected static ?string $title = 'Data Progress Tracker';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.data-progress-tracker';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:DataProgressTracker') ?? false;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DataProgressStatsWidget::class,
            DataProgressTableWidget::class,
        ];
    }
}
