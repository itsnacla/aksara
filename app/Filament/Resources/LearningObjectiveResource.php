<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningObjectiveResource\Pages\ListLearningObjectives;
use App\Filament\Resources\LearningObjectiveResource\Schemas\LearningObjectiveForm;
use App\Filament\Resources\LearningObjectiveResource\Tables\LearningObjectiveTable;
use App\Models\LearningObjective;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningObjectiveResource extends Resource
{
    protected static ?string $model = LearningObjective::class;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
            $query->whereIn('subject_id', $user->teacher->subjects()->pluck('subjects.id')->toArray());
        }
        return $query;
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static \UnitEnum|string|null $navigationGroup = 'Akademik & KBM';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tujuan Pembelajaran (TP)';

    protected static ?string $modelLabel = 'Tujuan Pembelajaran (TP)';

    protected static ?string $pluralModelLabel = 'Tujuan Pembelajaran (TP)';

    public static function form(Schema $schema): Schema
    {
        return LearningObjectiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningObjectiveTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLearningObjectives::route('/'),
        ];
    }
}
