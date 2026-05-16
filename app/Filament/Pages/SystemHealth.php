<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\SystemHealthWidget;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SystemHealth extends Page
{
    protected string $view = 'filament.pages.system-health';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shield-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public function getTitle(): string|Htmlable
    {
        return 'System Health';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SystemHealthWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Status')
                ->color('gray')
                ->icon('heroicon-m-arrow-path')
                ->action(function () {
                    $this->dispatch('refresh-stats');
                    
                    Notification::make()
                        ->title('Health Check Completed')
                        ->success()
                        ->send();
                }),
        ];
    }
}
