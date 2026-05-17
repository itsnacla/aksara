<?php

namespace App\Filament\Resources\Teachers;

use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\Pages\ViewTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Filament\Resources\Teachers\Schemas\TeacherForm;
use App\Filament\Resources\Teachers\Schemas\TeacherInfoList;
use App\Filament\Resources\Teachers\Tables\TeachersTable;
use App\Models\Teacher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-briefcase';

    protected static UnitEnum|string|null $navigationGroup = 'Data Kependidikan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Data Guru';

    protected static ?string $modelLabel = 'Guru';

    protected static ?string $pluralModelLabel = 'Guru';

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return $record?->user?->name;
    }

    public static function form(Schema $schema): Schema
    {
        return TeacherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeachersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeacherInfoList::configure($schema);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeachers::route('/'),
        ];
    }
}
