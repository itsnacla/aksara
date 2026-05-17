<?php

namespace App\Filament\Resources\LearningObjective\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->relationship('subject', 'nama_mapel', modifyQueryUsing: function ($query) {
                    $query->where('subjects.is_graded', true);
                    $user = auth()->user();
                    if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                        $teacherId = $user->teacher->id;
                        $managedLevelIds = \App\Models\StudyGroup::where('walikelas_id', $teacherId)->pluck('level_id')->toArray();
                        $query->where(function ($q) use ($teacherId, $managedLevelIds) {
                            $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                              ->orWhereHas('levels', fn ($lq) => $lq->whereIn('levels.id', $managedLevelIds));
                        });
                    }
                })
                ->label('Mata Pelajaran')
                ->searchable()
                ->preload()
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(function (callable $set, callable $get) {
                    $set('level_id', null);
                    $set('code', null);
                }),
                
            Select::make('level_id')
                ->label('Tingkatan / Fase')
                ->options(function (callable $get) {
                    $subjectId = $get('subject_id');
                    if (!$subjectId) {
                        return \App\Models\Level::pluck('nama_tingkatan', 'id')->toArray();
                    }
                    $subject = \App\Models\Subject::find($subjectId);
                    if ($subject) {
                        return $subject->levels->pluck('nama_tingkatan', 'id')->toArray();
                    }
                    return [];
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
                    if (!$subjectId || !$levelId) {
                        return [];
                    }
                    
                    $level = \App\Models\Level::find($levelId);
                    $tingkat = 1;
                    if ($level && preg_match('/\d+/', $level->nama_tingkatan, $matches)) {
                        $tingkat = (int)$matches[0];
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
                    if (!$subjectId || !$levelId) {
                        return null;
                    }
                    
                    $level = \App\Models\Level::find($levelId);
                    $tingkat = 1;
                    if ($level && preg_match('/\d+/', $level->nama_tingkatan, $matches)) {
                        $tingkat = (int)$matches[0];
                    }
                    
                    $lastLo = \App\Models\LearningObjective::where('subject_id', $subjectId)
                        ->where('level_id', $levelId)
                        ->where('code', 'like', "TP {$tingkat}.%")
                        ->get()
                        ->map(function ($lo) {
                            $parts = explode('.', $lo->code);
                            return isset($parts[1]) ? (int)$parts[1] : 0;
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
        if (!$subjectId || !$levelId) {
            $set('code', null);
            return;
        }
        
        $level = \App\Models\Level::find($levelId);
        $tingkat = 1;
        if ($level && preg_match('/\d+/', $level->nama_tingkatan, $matches)) {
            $tingkat = (int)$matches[0];
        }
        
        $lastLo = \App\Models\LearningObjective::where('subject_id', $subjectId)
            ->where('level_id', $levelId)
            ->where('code', 'like', "TP {$tingkat}.%")
            ->get()
            ->map(function ($lo) {
                $parts = explode('.', $lo->code);
                return isset($parts[1]) ? (int)$parts[1] : 0;
            })
            ->max();
            
        $nextNum = ($lastLo ?? 0) + 1;
        $set('code', "TP {$tingkat}.{$nextNum}");
    }
}
