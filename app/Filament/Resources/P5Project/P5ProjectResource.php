<?php

namespace App\Filament\Resources\P5Project;

use App\Filament\Resources\P5Project\Pages;
use App\Filament\Resources\P5Project\Schemas\P5ProjectForm;
use App\Filament\Resources\P5Project\Tables\P5ProjectTable;
use App\Models\P5Project;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class P5ProjectResource extends Resource
{
    protected static ?string $model = P5Project::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'p5-project';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengembangan Diri';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kokurikuler';

    protected static ?string $modelLabel = 'Kegiatan Kokurikuler';

    protected static ?string $pluralModelLabel = 'Kegiatan Kokurikuler';

    public static function form(Schema $schema): Schema
    {
        return P5ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return P5ProjectTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // Filter by active academic year
        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
        if ($activeYearId) {
            $query->where('academic_year_id', $activeYearId);
        }
        
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            'groups' => RelationManagers\P5GroupRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListP5Projects::route('/'),
            'create' => Pages\CreateP5Project::route('/create'),
            'edit' => Pages\EditP5Project::route('/{record}'),
        ];
    }
}
