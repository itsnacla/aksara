<?php

namespace App\Filament\Resources\StudentParents;

use App\Filament\Resources\StudentParents\Pages\ListStudentParents;
use App\Filament\Resources\StudentParents\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\StudentParents\Schemas\StudentParentForm;
use App\Filament\Resources\StudentParents\Schemas\StudentParentInfoList;
use App\Filament\Resources\StudentParents\Tables\StudentParentsTable;
use App\Models\StudentParent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StudentParentResource extends Resource
{
    protected static ?string $model = StudentParent::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Data Wali Murid';

    protected static ?string $modelLabel = 'Wali Murid';

    protected static ?string $pluralModelLabel = 'Wali Murid';

    public static function getRecordTitle(?Model $record): ?string
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

    public static function getEloquentQuery(): Builder
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
