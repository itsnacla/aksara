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

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static UnitEnum|string|null $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Data Wali Murid';

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return $record?->user?->name;
    }

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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('user');
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
        ];
    }
}
