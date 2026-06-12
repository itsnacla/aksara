<?php

namespace App\Filament\Resources\WhatsAppLogs;

use App\Filament\Resources\WhatsAppLogs\Pages\ManageWhatsAppLogs;
use App\Models\WhatsAppLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsAppLogResource extends Resource
{
    protected static ?string $model = WhatsAppLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
    protected static \UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';
    protected static ?string $navigationLabel = 'Log WhatsApp';
    protected static ?string $modelLabel = 'Log WhatsApp';
    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal/Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor Tujuan')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->limit(50)
                    ->tooltip(function (\Filament\Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                \Filament\Tables\Columns\TextColumn::make('response')
                    ->label('Response API')
                    ->limit(50)
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWhatsAppLogs::route('/'),
        ];
    }
}
