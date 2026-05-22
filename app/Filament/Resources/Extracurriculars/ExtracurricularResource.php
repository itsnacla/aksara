<?php

namespace App\Filament\Resources\Extracurriculars;

use App\Filament\Resources\Extracurriculars\Pages\EditExtracurricular;
use App\Filament\Resources\Extracurriculars\Pages\ListExtracurriculars;
use App\Filament\Resources\Extracurriculars\RelationManagers\ExtracurricularStudentRelationManager;
use App\Filament\Resources\Extracurriculars\Schemas\ExtracurricularForm;
use App\Filament\Resources\Extracurriculars\Tables\ExtracurricularsTable;
use App\Models\Extracurricular;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ExtracurricularResource extends Resource
{
    protected static ?string $model = Extracurricular::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-trophy';

    protected static UnitEnum|string|null $navigationGroup = 'Pengembangan Diri & P5';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Ekstrakurikuler';

    protected static ?string $modelLabel = 'Ekstrakurikuler';

    protected static ?string $pluralModelLabel = 'Ekstrakurikuler';

    protected static ?string $recordTitleAttribute = 'nama_ekskul';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('coordinator');
    }

    public static function form(Schema $schema): Schema
    {
        return ExtracurricularForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExtracurricularsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ExtracurricularStudentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExtracurriculars::route('/'),
            'edit' => EditExtracurricular::route('/{record}/edit'),
        ];
    }
}
