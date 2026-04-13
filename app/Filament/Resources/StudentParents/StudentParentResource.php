<?php

namespace App\Filament\Resources\StudentParents;

use App\Filament\Resources\StudentParents\Pages\CreateStudentParent;
use App\Filament\Resources\StudentParents\Pages\EditStudentParent;
use App\Filament\Resources\StudentParents\Pages\ListStudentParents;
use App\Filament\Resources\StudentParents\Pages\ViewStudentParent;
use App\Filament\Resources\StudentParents\Schemas\StudentParentForm;
use App\Filament\Resources\StudentParents\Schemas\StudentParentInfoList;
use App\Filament\Resources\StudentParents\Tables\StudentParentsTable;
use App\Models\StudentParent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

use App\Filament\Resources\StudentParents\RelationManagers\StudentsRelationManager;

class StudentParentResource extends Resource
{
    protected static ?string $model = StudentParent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen User';

    protected static ?int $navigationSort = 12;

    protected static ?string $recordTitleAttribute = 'nama_wali';

    public static function form(Schema $schema): Schema
    {
        return StudentParentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentParentInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentParentsTable::configure($table);
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
            'index' => ListStudentParents::route('/'),
            'create' => CreateStudentParent::route('/create'),
            'view' => ViewStudentParent::route('/{record}'),
            'edit' => EditStudentParent::route('/{record}/edit'),
        ];
    }
}
