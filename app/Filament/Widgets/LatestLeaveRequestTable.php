<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLeaveRequestTable extends BaseWidget
{
    protected static ?string $heading = 'Pengajuan Izin Terbaru';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LeaveRequest::query()
                    ->with('user')
                    ->latest()
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                TextColumn::make('permission')
                    ->label('Jenis')
                    ->badge()
                    ->color('info'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('is_approved')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Disetujui' : 'Menunggu')
                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-clock'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->since()
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Belum ada pengajuan izin')
            ->emptyStateDescription('Pengajuan izin dari guru dan staff akan tampil disini.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
