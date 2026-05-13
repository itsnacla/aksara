<?php

namespace App\Filament\Widgets;

use App\Models\StudentLeave;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLeaveRequestTable extends BaseWidget
{
    protected static ?string $heading = 'Pengajuan Izin Siswa Terbaru';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StudentLeave::query()
                    ->with('student.user')
                    ->latest()
            )
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => $state === 'sakit' ? 'warning' : 'info'),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Belum ada pengajuan izin')
            ->emptyStateDescription('Pengajuan izin dari orang tua akan tampil disini.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
