<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\StudyGroup;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Closure;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('id'),
                
                Fieldset::make('Informasi Rombel & Mapel')
                    ->schema([
                        Select::make('study_group_id')
                            ->label('Rombel')
                            ->options(fn () => StudyGroup::with('academicYear')->whereHas('academicYear', fn ($q) => $q->where('is_active', true))->get()->mapWithKeys(fn ($rombel) => [
                                $rombel->id => "{$rombel->nama_rombel} ({$rombel->academicYear->tahun_ajaran})"
                            ]))
                            ->required()
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;
                                
                                $subjectId = $get('subject_id');
                                if ($subjectId) {
                                    $subject = Subject::find($subjectId);
                                    if ($subject && $subject->is_umum) {
                                        $studyGroup = StudyGroup::find($state);
                                        if ($studyGroup && $studyGroup->walikelas_id) {
                                            $set('teacher_id', $studyGroup->walikelas_id);
                                        }
                                    }
                                }
                            }),

                        Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->options(function (Get $get) {
                                $rombelId = $get('study_group_id');
                                if (!$rombelId) return Subject::pluck('nama_mapel', 'id')->toArray();

                                $studyGroup = StudyGroup::find($rombelId);
                                if (!$studyGroup) return Subject::pluck('nama_mapel', 'id')->toArray();

                                return Subject::where(function($q) use ($studyGroup) {
                                    $q->whereHas('levels', fn($sq) => $sq->where('levels.id', $studyGroup->level_id))
                                      ->orDoesntHave('levels');
                                })->get()->filter(function ($subject) use ($rombelId, $get) {
                                    $usedJp = Schedule::where('study_group_id', $rombelId)
                                        ->where('subject_id', $subject->id)
                                        ->where('id', '!=', $get('id'))
                                        ->get()
                                        ->sum(function($s) {
                                            $start = TimeSlot::find($s->start_time_slot_id);
                                            $end = TimeSlot::find($s->end_time_slot_id);
                                            return ($start && $end) ? abs($end->urutan - $start->urutan) + 1 : 0;
                                        });
                                    
                                    return ($subject->total_jp > $usedJp) || ($get('subject_id') == $subject->id);
                                })->pluck('nama_mapel', 'id')->toArray();
                            })
                            ->required()
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;
                                
                                $subject = Subject::find($state);
                                $rombelId = $get('study_group_id');

                                // Hitung Sisa JP
                                if ($rombelId) {
                                    $usedJp = Schedule::where('study_group_id', $rombelId)
                                        ->where('subject_id', $state)
                                        ->where('id', '!=', $get('id'))
                                        ->get()
                                        ->sum(function($s) {
                                            $start = TimeSlot::find($s->start_time_slot_id);
                                            $end = TimeSlot::find($s->end_time_slot_id);
                                            return ($start && $end) ? abs($end->urutan - $start->urutan) + 1 : 0;
                                        });
                                    
                                    $totalJp = $subject->total_jp ?? 0;
                                    $remaining = max(0, $totalJp - $usedJp);
                                    $set('remaining_jp', $remaining);

                                    // OTOMATIS CARI SLOT JIKA HARI SUDAH TERPILIH
                                    $hari = $get('hari');
                                    if ($hari) {
                                        $studyGroup = StudyGroup::find($rombelId);
                                        $allSlots = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))->orderBy('urutan')->get();
                                        
                                        foreach ($allSlots as $slot) {
                                            $isBusy = Schedule::where('hari', $hari)->where('study_group_id', $rombelId)
                                                ->where(fn($q) => $q->where('start_time_slot_id', '<=', $slot->id)->where('end_time_slot_id', '>=', $slot->id))
                                                ->exists();
                                            
                                            if (!$isBusy && !$slot->is_istirahat) {
                                                $set('start_time_slot_id', $slot->id);
                                                $duration = min($remaining, $subject->default_jp ?? 2);
                                                $endSlot = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                                    ->where('is_istirahat', false)->where('urutan', '>=', $slot->urutan)
                                                    ->orderBy('urutan')->take($duration)->get()->last();
                                                if ($endSlot) $set('end_time_slot_id', $endSlot->id);
                                                break;
                                            }
                                        }
                                    }
                                }

                                if ($subject && $subject->is_umum) {
                                    $studyGroup = StudyGroup::find($rombelId);
                                    if ($studyGroup && $studyGroup->walikelas_id) {
                                        $set('teacher_id', $studyGroup->walikelas_id);
                                    }
                                } else {
                                    $set('teacher_id', null);
                                }
                            })
                            ->helperText(fn (Get $get) => "Beban Mingguan: " . (Subject::find($get('subject_id'))?->total_jp ?? 0) . " JP | Sisa: " . ($get('remaining_jp') ?? 0) . " JP"),
                        
                        Hidden::make('remaining_jp'),
                    ])
                    ->columns(1),

                Fieldset::make('Pengajar & Waktu')
                    ->schema([
                        Select::make('teacher_id')
                            ->label('Guru Pengampu')
                            ->options(function (Get $get) {
                                $subjectId = $get('subject_id');
                                if (!$subjectId) return [];

                                $subject = Subject::find($subjectId);
                                if ($subject && $subject->is_umum) {
                                    $studyGroup = StudyGroup::find($get('study_group_id'));
                                    if ($studyGroup && $studyGroup->walikelas_id) {
                                        $teacher = Teacher::with('user')->find($studyGroup->walikelas_id);
                                        if ($teacher && $teacher->status === 'aktif' && $teacher->user?->is_active) {
                                            return [$teacher->id => $teacher->user->name . ' (Wali Kelas)'];
                                        }
                                    }
                                    return []; // Kembalikan kosong jika walikelas tidak aktif
                                }

                                return Teacher::where('status', 'aktif')
                                    ->whereHas('user', fn ($q) => $q->where('is_active', true))
                                    ->whereHas('subjects', function ($query) use ($subjectId) {
                                        $query->where('subjects.id', $subjectId);
                                    })->with('user')->get()->pluck('user.name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Get $get) => Subject::find($get('subject_id'))?->is_umum ?? false)
                            ->dehydrated() // Agar tetap terkirim meskipun disabled
                            ->helperText(fn (Get $get) => Subject::find($get('subject_id'))?->is_umum ? 'Otomatis Wali Kelas' : 'Pilih guru spesialis.'),

                        Select::make('hari')
                            ->label('Hari')
                            ->options([
                                'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state || !$get('study_group_id')) return;
                                
                                $rombelId = $get('study_group_id');
                                $studyGroup = StudyGroup::find($rombelId);
                                if (!$studyGroup) return;

                                // OTOMATISASI UPACARA HARI SENIN
                                if ($state === 'Senin') {
                                    $upacara = Subject::where('nama_mapel', 'like', '%Upacara%')->first();
                                    if ($upacara) {
                                        $alreadyScheduled = Schedule::where('hari', 'Senin')
                                            ->where('study_group_id', $rombelId)
                                            ->where('subject_id', $upacara->id)
                                            ->exists();
                                        
                                        if (!$alreadyScheduled) {
                                            $set('subject_id', $upacara->id);
                                            // Upacara biasanya JP pertama
                                            $firstSlot = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                                ->orderBy('urutan')->first();
                                            if ($firstSlot) {
                                                $set('start_time_slot_id', $firstSlot->id);
                                                $set('end_time_slot_id', $firstSlot->id);
                                            }
                                            return;
                                        }
                                    }
                                }

                                // Cari slot pertama yang benar-benar kosong (tidak bentrok rombel & tidak bentrok guru)
                                $allSlots = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                    ->where('is_istirahat', false)
                                    ->orderBy('urutan')
                                    ->get();

                                $teacherId = $get('teacher_id');

                                foreach ($allSlots as $slot) {
                                    $rombelBusy = Schedule::where('hari', $state)
                                        ->where('study_group_id', $rombelId)
                                        ->where('id', '!=', $get('id'))
                                        ->where(fn($q) => $q->where('start_time_slot_id', '<=', $slot->id)->where('end_time_slot_id', '>=', $slot->id))
                                        ->exists();
                                    
                                    $teacherBusy = false;
                                    if ($teacherId) {
                                        $teacherBusy = Schedule::where('hari', $state)
                                            ->where('teacher_id', $teacherId)
                                            ->where('id', '!=', $get('id'))
                                            ->where(fn($q) => $q->where('start_time_slot_id', '<=', $slot->id)->where('end_time_slot_id', '>=', $slot->id))
                                            ->exists();
                                    }

                                    if (!$rombelBusy && !$teacherBusy) {
                                        $set('start_time_slot_id', $slot->id);
                                        $remaining = $get('remaining_jp') ?? 2;
                                        $duration = min($remaining, 2);
                                        $endSlot = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                            ->where('is_istirahat', false)
                                            ->where('urutan', '>=', $slot->urutan)
                                            ->orderBy('urutan')->take($duration)->get()->last();
                                        if ($endSlot) $set('end_time_slot_id', $endSlot->id);
                                        break;
                                    }
                                }
                            }),

                        Select::make('start_time_slot_id')
                            ->label('Mulai Jam Ke-')
                            ->options(function (Get $get, $record) {
                                $rombelId = $get('study_group_id');
                                $hari = $get('hari');
                                $teacherId = $get('teacher_id');
                                $subjectId = $get('subject_id');
                                
                                $studyGroup = StudyGroup::with('academicYear')->find($rombelId);
                                if (!$studyGroup) return [];

                                // Ambil Aturan Hari
                                $dayConfig = \App\Models\DayConfig::with(['maxTimeSlot'])
                                    ->where('academic_year_id', $studyGroup->academic_year_id)
                                    ->where('day', $hari)
                                    ->whereJsonContains('level_ids', (int) $studyGroup->level_id)
                                    ->first();

                                if ($dayConfig && $dayConfig->is_closed) return [];

                                $slots = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                    ->where('is_istirahat', false)
                                    ->orderBy('urutan')
                                    ->get();

                                return $slots->filter(function ($slot) use ($rombelId, $hari, $teacherId, $get, $dayConfig, $subjectId) {
                                    if (!$hari) return true;

                                    // 1. Cek Batas Jam Maksimal
                                    if ($dayConfig && $dayConfig->maxTimeSlot && $slot->urutan > $dayConfig->maxTimeSlot->urutan) {
                                        return false;
                                    }

                                    // 2. Cek Aturan Wajib (Mandatory)
                                    if ($dayConfig && $dayConfig->mandatory_subject_id && $dayConfig->mandatory_time_slot_id) {
                                        // Jika slot ini adalah slot wajib untuk mapel LAIN, maka slot ini tertutup
                                        if ($slot->id == $dayConfig->mandatory_time_slot_id && $subjectId != $dayConfig->mandatory_subject_id) {
                                            return false;
                                        }
                                    }

                                    // 3. Eliminasi jam yang sudah terisi Rombel
                                    $rombelBusy = Schedule::where('hari', $hari)
                                        ->where('study_group_id', $rombelId)
                                        ->where('id', '!=', $get('id'))
                                        ->where(function($q) use ($slot) {
                                            $q->whereHas('startTimeSlot', fn($sq) => $sq->where('urutan', '<=', $slot->urutan))
                                              ->whereHas('endTimeSlot', fn($sq) => $sq->where('urutan', '>=', $slot->urutan));
                                        })
                                        ->exists();
                                    if ($rombelBusy) return false;

                                    // 4. Eliminasi jam yang sudah terisi Guru
                                    if ($teacherId) {
                                        $teacherBusy = Schedule::where('hari', $hari)
                                            ->where('teacher_id', $teacherId)
                                            ->where('id', '!=', $get('id'))
                                            ->where(function($q) use ($slot) {
                                                $q->whereHas('startTimeSlot', fn($sq) => $sq->where('urutan', '<=', $slot->urutan))
                                                  ->whereHas('endTimeSlot', fn($sq) => $sq->where('urutan', '>=', $slot->urutan));
                                            })
                                            ->exists();
                                        if ($teacherBusy) return false;
                                    }

                                    return true;
                                })->pluck('nama_jam', 'id');
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('end_time_slot_id', null))
                            ->hint(fn (Get $get) => TimeSlot::find($get('start_time_slot_id')) ? "Mulai: " . TimeSlot::find($get('start_time_slot_id'))->waktu_mulai->format('H:i') : null),

                        Select::make('end_time_slot_id')
                            ->label('Sampai Jam Ke-')
                            ->options(function (Get $get) {
                                $startId = $get('start_time_slot_id');
                                if (!$startId) return [];
                                
                                $startSlot = TimeSlot::find($startId);
                                $rombelId = $get('study_group_id');
                                $hari = $get('hari');
                                $teacherId = $get('teacher_id');
                                $subjectId = $get('subject_id');
                                $studyGroup = StudyGroup::with('academicYear')->find($rombelId);
                                
                                if (!$studyGroup) return [];

                                $dayConfig = \App\Models\DayConfig::with(['maxTimeSlot'])
                                    ->where('academic_year_id', $studyGroup->academic_year_id)
                                    ->where('day', $hari)
                                    ->whereJsonContains('level_ids', (int) $studyGroup->level_id)
                                    ->first();

                                if ($dayConfig && $dayConfig->is_closed) return [];

                                $query = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                    ->where('urutan', '>=', $startSlot->urutan)
                                    ->orderBy('urutan');
                                
                                return $query->get()->filter(function ($slot) use ($startSlot, $rombelId, $hari, $teacherId, $get, $studyGroup, $dayConfig, $subjectId) {
                                    // 1. Cek Batas Jam Maksimal
                                    if ($dayConfig && $dayConfig->maxTimeSlot && $slot->urutan > $dayConfig->maxTimeSlot->urutan) {
                                        return false;
                                    }

                                    // 2. Cek Aturan Wajib Mapel Lain yang ada di tengah-tengah rentang
                                    if ($dayConfig && $dayConfig->mandatory_subject_id && $dayConfig->mandatory_time_slot_id) {
                                        $mandatorySlot = TimeSlot::find($dayConfig->mandatory_time_slot_id);
                                        if ($mandatorySlot && $subjectId != $dayConfig->mandatory_subject_id) {
                                            // Jika rentang dari start sampai slot ini melewati jam wajib mapel lain
                                            if ($startSlot->urutan <= $mandatorySlot->urutan && $slot->urutan >= $mandatorySlot->urutan) {
                                                return false;
                                            }
                                        }
                                    }

                                    // 3. Jangan biarkan melewati jam yang sudah terisi (Eliminasi Bentrok)
                                    if ($hari) {
                                        $clashRombel = Schedule::where('hari', $hari)
                                            ->where('study_group_id', $rombelId)
                                            ->where('id', '!=', $get('id'))
                                            ->whereHas('startTimeSlot', fn($sq) => $sq->where('urutan', '>', $startSlot->urutan)->where('urutan', '<=', $slot->urutan))
                                            ->exists();
                                        if ($clashRombel) return false;

                                        if ($teacherId) {
                                            $clashTeacher = Schedule::where('hari', $hari)
                                                ->where('teacher_id', $teacherId)
                                                ->where('id', '!=', $get('id'))
                                                ->whereHas('startTimeSlot', fn($sq) => $sq->where('urutan', '>', $startSlot->urutan)->where('urutan', '<=', $slot->urutan))
                                                ->exists();
                                            if ($clashTeacher) return false;
                                        }
                                    }
                                    return true;
                                })->mapWithKeys(function ($slot) use ($startSlot, $studyGroup) {
                                    $jpCount = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                        ->where('is_istirahat', false)
                                        ->where('urutan', '>=', $startSlot->urutan)
                                        ->where('urutan', '<=', $slot->urutan)
                                        ->count();
                                    
                                    $label = $slot->is_istirahat ? "{$slot->nama_jam} (ISTIRAHAT)" : "{$slot->nama_jam} (Total {$jpCount} JP)";
                                    return [$slot->id => $label];
                                });
                            })
                            ->required()
                            ->live()
                            ->hint(fn (Get $get) => TimeSlot::find($get('end_time_slot_id')) ? "Selesai: " . TimeSlot::find($get('end_time_slot_id'))->waktu_selesai->format('H:i') : null)
                            ->helperText(function (Get $get) {
                                $startId = $get('start_time_slot_id');
                                $endId = $get('end_time_slot_id');
                                $rombelId = $get('study_group_id');
                                if ($startId && $endId && $rombelId) {
                                    $studyGroup = StudyGroup::find($rombelId);
                                    $startSlot = TimeSlot::find($startId);
                                    $endSlot = TimeSlot::find($endId);
                                    
                                    $jpCount = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                        ->where('is_istirahat', false)
                                        ->where('urutan', '>=', $startSlot->urutan)
                                        ->where('urutan', '<=', $endSlot->urutan)
                                        ->count();
                                        
                                    return "Sesi ini akan menghabiskan {$jpCount} JP dari kuota mingguan.";
                                }
                                return "Pilih jam mulai dan jam selesai.";
                            })
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                        $recordId = $get('id');
                                        $hari = $get('hari');
                                        $startId = $get('start_time_slot_id');
                                        $endId = $value;
                                        $teacherId = $get('teacher_id');
                                        $rombelId = $get('study_group_id');
                                        if (!$hari || !$startId || !$endId || !$teacherId || !$rombelId) return;

                                        $studyGroup = StudyGroup::find($rombelId);
                                        $startSlot = TimeSlot::find($startId);
                                        $endSlot = TimeSlot::find($endId);

                                        // Validasi Beban JP
                                        $jpCount = TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                                            ->where('is_istirahat', false)
                                            ->where('urutan', '>=', $startSlot->urutan)
                                            ->where('urutan', '<=', $endSlot->urutan)
                                            ->count();

                                        $remaining = $get('remaining_jp') ?? 0;
                                        if ($jpCount > $remaining) {
                                            $fail("Total {$jpCount} JP melebihi sisa beban mingguan Mapel ini ({$remaining} JP)!");
                                        }

                                        $conflictTeacher = Schedule::where('hari', $hari)
                                            ->where('teacher_id', $teacherId)
                                            ->where('id', '!=', $recordId)
                                            ->where(function ($q) use ($startId, $endId) {
                                                $q->where('start_time_slot_id', '<=', $endId)
                                                   ->where('end_time_slot_id', '>=', $startId);
                                            })->first();
                                        if ($conflictTeacher) {
                                            $fail("Guru tersebut sudah ada jadwal di Rombel: {$conflictTeacher->studyGroup->nama_rombel} pada jam yang sama!");
                                        }

                                        $conflictRombel = Schedule::where('hari', $hari)
                                            ->where('study_group_id', $rombelId)
                                            ->where('id', '!=', $recordId)
                                            ->where(function ($q) use ($startId, $endId) {
                                                $q->where('start_time_slot_id', '<=', $endId)
                                                   ->where('end_time_slot_id', '>=', $startId);
                                            })->first();
                                        if ($conflictRombel) {
                                            $fail("Rombel ini sudah memiliki jadwal Mapel: {$conflictRombel->subject->nama_mapel} pada jam yang sama!");
                                        }
                                    };
                                },
                            ]),
                    ])
                    ->columns(1),
            ]);
    }
}
