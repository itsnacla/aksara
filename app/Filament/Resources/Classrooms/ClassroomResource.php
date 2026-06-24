<?php

namespace App\Filament\Resources\Classrooms;

use App\Filament\Resources\Classrooms\Pages\CreateClassroom;
use App\Filament\Resources\Classrooms\Pages\EditClassroom;
use App\Filament\Resources\Classrooms\Pages\ListClassrooms;
use App\Filament\Resources\Classrooms\Schemas\ClassroomForm;
use App\Filament\Resources\Classrooms\Tables\ClassroomsTable;
use App\Models\Classroom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $modelLabel = 'Ruangan';

    protected static ?string $pluralModelLabel = 'Ruangan';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';

    protected static UnitEnum|string|null $navigationGroup = 'Kurikulum & Referensi';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Ruangan';

    protected static ?string $recordTitleAttribute = 'nama_ruangan';

    public static function form(Schema $schema): Schema
    {
        return ClassroomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassroomsTable::configure($table);
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
            'index' => ListClassrooms::route('/'),
        ];
    }
}
