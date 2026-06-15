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
            
            if (auth()->user() && auth()->user()->hasRole('guru')) {
                $teacherId = auth()->user()->teacher?->id;
                if ($teacherId) {
                    $query->where(function($q) use ($teacherId) {
                        $q->where('walikelas_id', $teacherId)
                          ->orWhereHas('schedules', fn($sq) => $sq->where('teacher_id', $teacherId));
                    });
                } else {
                    $query->where('id', 0);
                }
            }
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
                    ->searchable(false)
                    ->sortable(false)
                    ->default('-'),

                Tables\Columns\TextColumn::make('progress_nilai')
                    ->label('Progress Nilai')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (!$activeYear) return '0 / 0 (0%)';
                        
                        $studentCount = $record->students()->count();
                        $scheduleCount = $record->schedules()
                            ->whereHas('subject', function ($sq) {
                                $sq->where('is_graded', true);
                            })
                            ->count();
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

                Tables\Columns\TextColumn::make('progress_presensi')
                    ->label('Progress Presensi (Hari Ini)')
                    ->getStateUsing(function (StudyGroup $record) {
                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();
                        
                        $current = \App\Models\Attendance::whereIn('student_id', $studentIds)
                            ->where('tanggal', now()->toDateString())
                            ->count();
                            
                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) $percent = 100;
                        
                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_ekskul')
                    ->label('Progress Ekskul')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (!$activeYear) return '0 / 0 (0%)';
                        
                        $studentIds = $record->students()->pluck('students.id');
                        
                        $expected = \Illuminate\Support\Facades\DB::table('extracurricular_student')
                            ->whereIn('student_id', $studentIds)
                            ->count();
                            
                        $current = \App\Models\ExtracurricularGrade::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->whereExists(function($q) {
                                $q->selectRaw('1')
                                  ->from('extracurricular_student')
                                  ->whereColumn('extracurricular_student.student_id', 'extracurricular_grades.student_id')
                                  ->whereColumn('extracurricular_student.extracurricular_id', 'extracurricular_grades.extracurricular_id');
                            })
                            ->count();
                            
                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) $percent = 100;
                        
                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_catatan_wali')
                    ->label('Catatan Wali')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (!$activeYear) return '0 / 0 (0%)';
                        
                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();
                        
                        $current = StudentRapor::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->whereNotNull('catatan_wali_kelas')
                            ->where('catatan_wali_kelas', '!=', '')
                            ->count();
                            
                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) $percent = 100;
                        
                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_publikasi')
                    ->label('Publikasi Rapor')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (!$activeYear) return '0 / 0 (0%)';
                        
                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();
                        
                        $current = StudentRapor::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->where('is_published', true)
                            ->count();
                            
                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) $percent = 100;
                        
                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'primary')),
            ])
            ->actions([
                \Filament\Actions\Action::make('cek_tanggungan')
                    ->label('Cek Tanggungan')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->modalHeading(fn (StudyGroup $record) => "Tanggungan: " . $record->nama_rombel)
                    ->modalContent(function (StudyGroup $record) use ($activeYear) {
                        $tanggungan = $this->getTanggungan($record, $activeYear);
                        return view('filament.widgets.tanggungan-rombel', [
                            'tanggungan' => $tanggungan,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
            ])
            ->recordAction('cek_tanggungan')
            ->defaultSort('nama_rombel', 'asc')
            ->paginated([10, 25, 50, 'all']);
    }

    private function getTanggungan(StudyGroup $record, $activeYear): array
    {
        $tanggungan = [];
        if (!$activeYear) return $tanggungan;

        $studentIds = $record->students()->pluck('students.id');
        
        if ($nilai = $this->getTanggunganNilai($record, $activeYear, $studentIds)) $tanggungan[] = $nilai;
        if ($rapor = $this->getTanggunganCetakRapor($record, $activeYear, $studentIds)) $tanggungan[] = $rapor;
        if ($presensi = $this->getTanggunganPresensi($record, $activeYear, $studentIds)) $tanggungan[] = $presensi;
        if ($ekskul = $this->getTanggunganEkskul($record, $activeYear, $studentIds)) $tanggungan[] = $ekskul;
        if ($catatan = $this->getTanggunganCatatanWali($record, $activeYear, $studentIds)) $tanggungan[] = $catatan;
        if ($publikasi = $this->getTanggunganPublikasiRapor($record, $activeYear, $studentIds)) $tanggungan[] = $publikasi;

        return $tanggungan;
    }

    private function getTanggunganNilai($record, $activeYear, $studentIds): ?array
    {
        $gradedSubjectsCount = \App\Models\Schedule::where('study_group_id', $record->id)
            ->whereHas('subject', fn ($sq) => $sq->where('is_graded', true))
            ->distinct('subject_id')
            ->count('subject_id');
        $expectedGrades = $studentIds->count() * $gradedSubjectsCount;
        $currentGrades = Grade::where('academic_year_id', $activeYear->id)
            ->where('study_group_id', $record->id)
            ->count();
        if ($expectedGrades > 0 && $currentGrades < $expectedGrades) {
            $percent = round(($currentGrades / $expectedGrades) * 100, 1);
            return [
                'title' => 'Input Nilai',
                'text' => "{$currentGrades} / {$expectedGrades} ({$percent}%)",
                'url' => \App\Filament\Resources\Grades\GradeResource::getUrl('index', ['tableFilters' => ['study_group_id' => ['value' => $record->id]]]),
            ];
        }
        return null;
    }

    private function getTanggunganCetakRapor($record, $activeYear, $studentIds): ?array
    {
        $expectedRapor = $studentIds->count();
        $currentRapor = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->count();
        if ($expectedRapor > 0 && $currentRapor < $expectedRapor) {
            $percent = round(($currentRapor / $expectedRapor) * 100, 1);
            return [
                'title' => 'Cetak Rapor',
                'text' => "{$currentRapor} / {$expectedRapor} ({$percent}%)",
                'url' => \App\Filament\Resources\Rapor\RaporResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id]]]),
            ];
        }
        return null;
    }

    private function getTanggunganPresensi($record, $activeYear, $studentIds): ?array
    {
        $expectedPresensi = $studentIds->count();
        $currentPresensi = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->where('tanggal', now()->toDateString())
            ->count();
        if ($expectedPresensi > 0 && $currentPresensi < $expectedPresensi) {
            $percent = round(($currentPresensi / $expectedPresensi) * 100, 1);
            return [
                'title' => 'Presensi Hari Ini',
                'text' => "{$currentPresensi} / {$expectedPresensi} ({$percent}%)",
                'url' => \App\Filament\Resources\Attendances\AttendanceResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id]]]),
            ];
        }
        return null;
    }

    private function getTanggunganEkskul($record, $activeYear, $studentIds): ?array
    {
        $expectedEkskul = \Illuminate\Support\Facades\DB::table('extracurricular_student')
            ->whereIn('student_id', $studentIds)
            ->count();
        $currentEkskul = \App\Models\ExtracurricularGrade::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->whereExists(function($q) {
                $q->selectRaw('1')
                  ->from('extracurricular_student')
                  ->whereColumn('extracurricular_student.student_id', 'extracurricular_grades.student_id')
                  ->whereColumn('extracurricular_student.extracurricular_id', 'extracurricular_grades.extracurricular_id');
            })
            ->count();
        if ($expectedEkskul > 0 && $currentEkskul < $expectedEkskul) {
            $percent = round(($currentEkskul / $expectedEkskul) * 100, 1);
            return [
                'title' => 'Nilai Ekstrakurikuler',
                'text' => "{$currentEkskul} / {$expectedEkskul} ({$percent}%)",
                'url' => \App\Filament\Resources\ExtracurricularGrade\ExtracurricularGradeResource::getUrl('index', ['tableFilters' => ['study_group_id' => ['value' => $record->id]]]),
            ];
        }
        return null;
    }

    private function getTanggunganCatatanWali($record, $activeYear, $studentIds): ?array
    {
        $expectedCatatan = $studentIds->count();
        $currentCatatan = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('catatan_wali_kelas')
            ->where('catatan_wali_kelas', '!=', '')
            ->count();
        if ($expectedCatatan > 0 && $currentCatatan < $expectedCatatan) {
            $percent = round(($currentCatatan / $expectedCatatan) * 100, 1);
            return [
                'title' => 'Catatan Wali Kelas',
                'text' => "{$currentCatatan} / {$expectedCatatan} ({$percent}%)",
                'url' => \App\Filament\Resources\Rapor\RaporResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id]]]),
            ];
        }
        return null;
    }

    private function getTanggunganPublikasiRapor($record, $activeYear, $studentIds): ?array
    {
        $expectedPublikasi = $studentIds->count();
        $currentPublikasi = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->where('is_published', true)
            ->count();
        if ($expectedPublikasi > 0 && $currentPublikasi < $expectedPublikasi) {
            $percent = round(($currentPublikasi / $expectedPublikasi) * 100, 1);
            return [
                'title' => 'Publikasi Rapor',
                'text' => "{$currentPublikasi} / {$expectedPublikasi} ({$percent}%)",
                'url' => \App\Filament\Resources\Rapor\RaporResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id]]]),
            ];
        }
        return null;
    }
}
