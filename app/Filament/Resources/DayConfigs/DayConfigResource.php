<?php

namespace App\Filament\Resources\DayConfigs;

use App\Filament\Resources\DayConfigs\Pages;
use App\Filament\Resources\DayConfigs\Schemas\DayConfigForm;
use App\Models\DayConfig;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DayConfigResource extends Resource
{
    protected static ?string $model = DayConfig::class;

    protected static ?string $recordTitleAttribute = 'day';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-vertical';

    protected static UnitEnum|string|null $navigationGroup = 'Jadwal Pelajaran';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Aturan Hari';
    
    protected static ?string $modelLabel = 'Aturan Hari';

    protected static ?string $pluralModelLabel = 'Aturan Hari';

    public static function form(Schema $schema): Schema
    {
        return DayConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day')
                    ->label('Hari')
                    ->sortable()
                    ->badge(),
                \Filament\Tables\Columns\IconColumn::make('is_closed')
                    ->label('Libur')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('level_ids')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn ($state) => \App\Models\Level::whereIn('id', \Illuminate\Support\Arr::wrap($state))->pluck('nama_tingkatan')->toArray()),
                TextColumn::make('maxTimeSlot.nama_jam')
                    ->label('Maks Jam'),
                TextColumn::make('mandatorySubject.nama_mapel')
                    ->label('Mapel Wajib'),
                TextColumn::make('mandatoryTimeSlot.nama_jam')
                    ->label('Pada Jam'),
                TextColumn::make('academicYear.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('day')
                    ->label('Hari')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                    ]),
                SelectFilter::make('level_ids')
                    ->label('Level')
                    ->options(fn() => \App\Models\Level::pluck('nama_tingkatan', 'id'))
                    ->query(fn ($query, $data) => $query->when($data['value'], fn($q) => $q->whereJsonContains('level_ids', $data['value']))),
            ])
            ->actions([
                EditAction::make()->modal(),
                DeleteAction::make()->modal(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDayConfigs::route('/'),
        ];
    }
}
