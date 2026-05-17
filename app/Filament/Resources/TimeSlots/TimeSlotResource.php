<?php

namespace App\Filament\Resources\TimeSlots;

use App\Filament\Resources\TimeSlots\Pages;
use App\Filament\Resources\TimeSlots\Schemas\TimeSlotForm;
use App\Filament\Resources\TimeSlots\Tables\TimeSlotsTable;
use App\Models\TimeSlot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TimeSlotResource extends Resource
{
    protected static ?string $model = TimeSlot::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static UnitEnum|string|null $navigationGroup = 'Jadwal Pelajaran';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Slot Waktu';

    protected static ?string $modelLabel = 'Slot Waktu';

    protected static ?string $pluralModelLabel = 'Slot Waktu';

    public static function form(Schema $schema): Schema
    {
        return TimeSlotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeSlotsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTimeSlots::route('/'),
        ];
    }
}
