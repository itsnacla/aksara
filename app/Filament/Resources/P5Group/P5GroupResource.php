<?php

namespace App\Filament\Resources\P5Group;

use App\Filament\Resources\P5Group\Pages;
use App\Filament\Resources\P5Group\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\P5Group\Schemas\P5GroupForm;
use App\Filament\Resources\P5Group\Tables\P5GroupTable;
use App\Models\P5Group;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class P5GroupResource extends Resource
{
    protected static ?string $model = P5Group::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'p5-group';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengembangan Diri & P5';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Kelompok Kokurikuler';

    protected static ?string $modelLabel = 'Kelompok Kokurikuler';

    protected static ?string $pluralModelLabel = 'Kelompok Kokurikuler';

    public static function form(Schema $schema): Schema
    {
        return P5GroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return P5GroupTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListP5Groups::route('/'),
            'create' => Pages\CreateP5Group::route('/create'),
            'edit' => Pages\EditP5Group::route('/{record}/edit'),
        ];
    }
}
