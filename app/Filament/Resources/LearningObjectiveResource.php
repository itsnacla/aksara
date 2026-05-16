<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningObjectiveResource\Pages\ListLearningObjectives;
use App\Filament\Resources\LearningObjectiveResource\Schemas\LearningObjectiveForm;
use App\Filament\Resources\LearningObjectiveResource\Tables\LearningObjectiveTable;
use App\Models\LearningObjective;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LearningObjectiveResource extends Resource
{
    protected static ?string $model = LearningObjective::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Akademik';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Tujuan Pembelajaran (TP)';

    protected static ?string $pluralLabel = 'Tujuan Pembelajaran (TP)';

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
