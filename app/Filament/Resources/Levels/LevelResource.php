<?php

namespace App\Filament\Resources\Levels;

use App\Filament\Resources\Levels\Pages\CreateLevel;
use App\Filament\Resources\Levels\Pages\EditLevel;
use App\Filament\Resources\Levels\Pages\ListLevels;
use App\Filament\Resources\Levels\Schemas\LevelForm;
use App\Filament\Resources\Levels\Tables\LevelsTable;
use App\Models\Level;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static UnitEnum|string|null $navigationGroup = 'Kurikulum & Referensi';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Tingkatan';

    protected static ?string $modelLabel = 'Tingkatan';

    protected static ?string $pluralModelLabel = 'Tingkatan';

    protected static ?string $recordTitleAttribute = 'nama_tingkatan';

    public static function form(Schema $schema): Schema
    {
        return LevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLevels::route('/'),
        ];
    }
}
