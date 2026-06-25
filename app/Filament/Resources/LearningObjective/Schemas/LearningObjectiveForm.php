<?php

namespace App\Filament\Resources\LearningObjective\Schemas;

use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\Level;
use App\Models\StudyGroup;
use App\Models\Subject;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('academic_year_id')
                ->default(function () {
                    return AcademicYear::where('is_active', true)->value('id');
                })
                ->required(),

            Select::make('subject_id')
                ->relationship('subject', 'nama_mapel', modifyQueryUsing: function ($query) {
                    $query->where('subjects.is_graded', true);
                    $user = auth()->user();
                    if ($user && $user->hasRole('guru') && ! $user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                        $teacherId = $user->teacher->id;
                        $isWaliKelas = $user->teacher->is_walikelas;

                        if ($isWaliKelas) {
                            // Wali kelas can see: is_umum subjects OR subjects from schedules OR subjects from teacher relationship
                            $query->where(function ($q) use ($teacherId) {
                                $q->where('subjects.is_umum', true)
                                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                    ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                            });
                        } else {
                            // Guru mapel can see: subjects from schedules OR subjects from teacher relationship
                            $query->where(function ($q) use ($teacherId) {
                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                    ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                            });
                        }
                    }
                })
                ->label('Mata Pelajaran')
                ->searchable()
                ->preload()
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(function (callable $set, callable $get) {
                    $user = auth()->user();
                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                    if (! $isWaliKelas) {
                        // Guru mapel: reset level_id karena mereka pilih manual
                        $set('level_id', null);
                    }
                    // Untuk semua: reset code
                    $set('code', null);
                }),

            Select::make('level_id')
                ->label('Tingkatan / Fase')
                ->options(function (callable $get) {
                    $user = auth()->user();
                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                    if ($isWaliKelas) {
                        // Wali kelas: auto-populate dari rombel mereka
                        $managedStudyGroup = StudyGroup::where('walikelas_id', $user->teacher->id)
                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                            ->first();

                        if ($managedStudyGroup && $managedStudyGroup->level) {
                            return [$managedStudyGroup->level->id => $managedStudyGroup->level->nama_tingkatan];
                        }

                        return [];
                    }

                    // Guru mapel: manual select
                    $subjectId = $get('subject_id');
                    if (! $subjectId) {
                        return Level::pluck('nama_tingkatan', 'id')->toArray();
                    }
                    $subject = Subject::find($subjectId);
                    if ($subject) {
                        return $subject->levels->pluck('nama_tingkatan', 'id')->toArray();
                    }

                    return [];
                })
                ->default(function () {
                    $user = auth()->user();
                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                    if ($isWaliKelas) {
                        // Auto-populate level untuk wali kelas
                        $managedStudyGroup = StudyGroup::where('walikelas_id', $user->teacher->id)
                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                            ->first();

                        return $managedStudyGroup?->level_id;
                    }

                    return null;
                })
                ->disabled(function () {
                    $user = auth()->user();

                    return $user && $user->teacher && $user->teacher->is_walikelas;
                })
                ->dehydrated()
                ->helperText(function () {
                    $user = auth()->user();
                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                    if ($isWaliKelas) {
                        return 'Tingkatan otomatis diambil dari rombel yang Anda kelola.';
                    }

                    return null;
                })
                ->searchable()
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(fn (callable $set, callable $get) => self::updateNextCode($set, $get)),

            Select::make('code')
                ->label('Kode TP')
                ->placeholder('Pilih Kode TP')
                ->options(function (callable $get) {
                    $subjectId = $get('subject_id');
                    $levelId = $get('level_id');
                    if (! $subjectId || ! $levelId) {
                        return [];
                    }

                    $level = Level::find($levelId);
                    $tingkat = 1;
                    if ($level && preg_match('/\d+/', $level->nama_tingkatan, $matches)) {
                        $tingkat = (int) $matches[0];
                    }

                    $options = [];
                    for ($i = 1; $i <= 15; $i++) {
                        $codeStr = "TP {$tingkat}.{$i}";
                        $options[$codeStr] = $codeStr;
                    }

                    return $options;
                })
                ->default(function (callable $get) {
                    $subjectId = $get('subject_id');
                    $levelId = $get('level_id');
                    if (! $subjectId || ! $levelId) {
                        return null;
                    }

                    $level = Level::find($levelId);
                    $tingkat = 1;
                    if ($level && preg_match('/\d+/', $level->nama_tingkatan, $matches)) {
                        $tingkat = (int) $matches[0];
                    }

                    $lastLo = LearningObjective::where('subject_id', $subjectId)
                        ->where('level_id', $levelId)
                        ->where('code', 'like', "TP {$tingkat}.%")
                        ->get()
                        ->map(function ($lo) {
                            $parts = explode('.', $lo->code);

                            return isset($parts[1]) ? (int) $parts[1] : 0;
                        })
                        ->max();

                    $nextNum = ($lastLo ?? 0) + 1;

                    return "TP {$tingkat}.{$nextNum}";
                })
                ->searchable()
                ->required()
                ->native(false),

            Toggle::make('is_active')
                ->label('Status Aktif')
                ->default(true),

            Textarea::make('description')
                ->label('Deskripsi Tujuan Pembelajaran')
                ->placeholder('Contoh: Menjelaskan proses fotosintesis pada tumbuhan secara sederhana.')
                ->required()
                ->maxLength(200)
                ->helperText('Maksimal 200 karakter. Buat deskripsi yang singkat, padat, dan jelas.')
                ->columnSpanFull()
                ->rows(3),
        ]);
    }

    public static function updateNextCode(callable $set, callable $get): void
    {
        $subjectId = $get('subject_id');
        $levelId = $get('level_id');
        if (! $subjectId || ! $levelId) {
            $set('code', null);

            return;
        }

        $level = Level::find($levelId);
        $tingkat = 1;
        if ($level && preg_match('/\d+/', $level->nama_tingkatan, $matches)) {
            $tingkat = (int) $matches[0];
        }

        $lastLo = LearningObjective::where('subject_id', $subjectId)
            ->where('level_id', $levelId)
            ->where('code', 'like', "TP {$tingkat}.%")
            ->get()
            ->map(function ($lo) {
                $parts = explode('.', $lo->code);

                return isset($parts[1]) ? (int) $parts[1] : 0;
            })
            ->max();

        $nextNum = ($lastLo ?? 0) + 1;
        $set('code', "TP {$tingkat}.{$nextNum}");
    }
}
