<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\StudyGroup;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\StudentRapor;

class DataProgressTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Breakdown Progress per Rombel';

    public function table(Table $table): Table
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        $query = StudyGroup::query();
        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        } else {
            $query->where('id', 0); // Return empty if no active year
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('nama_rombel')
                    ->label('Nama Rombel')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('waliKelas.nama_lengkap')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('progress_nilai')
                    ->label('Progress Nilai')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (!$activeYear) return '0 / 0 (0%)';
                        
                        $studentCount = $record->students()->count();
                        $scheduleCount = $record->schedules()->count();
                        $expected = $studentCount * $scheduleCount;
                        
                        $current = Grade::where('academic_year_id', $activeYear->id)
                            ->where('study_group_id', $record->id)
                            ->count();
                            
                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) $percent = 100;
                        
                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_rapor')
                    ->label('Progress Cetak Rapor')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (!$activeYear) return '0 / 0 (0%)';
                        
                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();
                        
                        $current = StudentRapor::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->count();
                            
                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) $percent = 100;
                        
                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'primary')),
            ])
            ->defaultSort('nama_rombel', 'asc')
            ->paginated([10, 25, 50, 'all']);
    }
}
