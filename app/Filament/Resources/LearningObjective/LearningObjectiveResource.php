<?php

namespace App\Filament\Resources\LearningObjective;

use App\Filament\Resources\LearningObjective\Pages\ListLearningObjectives;
use App\Filament\Resources\LearningObjective\Schemas\LearningObjectiveForm;
use App\Filament\Resources\LearningObjective\Tables\LearningObjectiveTable;
use App\Models\LearningObjective;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningObjectiveResource extends Resource
{
    protected static ?string $model = LearningObjective::class;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $slug = 'learning-objective';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Filter by teacher role
        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
            $teacherId = $user->teacher->id;
            $isWaliKelas = $user->teacher->is_walikelas;
            
            if ($isWaliKelas) {
                // Wali kelas: only TP for mapel umum at their level (active academic year)
                $managedLevelIds = \App\Models\StudyGroup::where('walikelas_id', $teacherId)
                    ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                    ->pluck('level_id')
                    ->toArray();
                
                $query->where(function ($q) use ($managedLevelIds) {
                    $q->whereIn('level_id', $managedLevelIds)
                      ->whereHas('subject', fn ($sq) => $sq->where('is_umum', true));
                });
            } else {
                // Guru mapel: only TP for subjects they teach
                $query->whereHas('subject', function ($sq) use ($teacherId) {
                    $sq->whereHas('schedules', fn ($ssq) => $ssq->where('teacher_id', $teacherId))
                      ->orWhereIn('subjects.id', \DB::table('subject_teacher')->where('teacher_id', $teacherId)->pluck('subject_id'));
                });
            }
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
