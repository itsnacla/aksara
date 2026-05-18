<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate_jadwal')
                ->label('Auto-Generate Jadwal')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('study_group_ids')
                        ->label('Pilih Rombel')
                        ->options(fn() => \App\Models\StudyGroup::whereHas('academicYear', fn($q) => $q->where('is_active', true))->pluck('nama_rombel', 'id')->toArray())
                        ->multiple()
                        ->required()
                        ->searchable()
                        ->hint('Anda bisa memilih lebih dari satu rombel'),
                    \Filament\Forms\Components\Toggle::make('overwrite')
                        ->label('Hapus Jadwal Lama Rombel Ini?')
                        ->default(false),
                ])
                ->action(fn (array $data) => $this->autoGenerateSchedule($data)),
            \Filament\Actions\Action::make('cetak_jadwal')
                ->label('Cetak Jadwal')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->form([
                    Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Radio::make('print_mode')
                                ->label('Mode Cetak')
                                ->options([
                                    'global' => 'Global (Semua Rombel)',
                                    'single' => 'Satuan (Per Rombel)',
                                ])
                                ->default('single')
                                ->live(),
                            \Filament\Forms\Components\Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(\App\Models\StudyGroup::all()->pluck('nama_rombel', 'id'))
                                ->placeholder('Pilih Rombel...')
                                ->searchable()
                                ->visible(fn (Get $get) => $get('print_mode') === 'single')
                                ->required(fn (Get $get) => $get('print_mode') === 'single'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\Select::make('paper_size')
                                ->label('Ukuran Kertas')
                                ->options([
                                    'a4' => 'A4 (210 x 297 mm)',
                                    'f4' => 'F4 / Folio (215 x 330 mm)',
                                ])
                                ->default('a4')
                                ->required(),
                            \Filament\Forms\Components\Select::make('orientation')
                                ->label('Orientasi')
                                ->options([
                                    'portrait' => 'Portrait (Tegak)',
                                    'landscape' => 'Landscape (Miring)',
                                ])
                                ->default('landscape')
                                ->required(),
                            \Filament\Forms\Components\Toggle::make('show_subject_code')
                                ->label('Tampilkan Kode Mapel')
                                ->default(true),
                            \Filament\Forms\Components\Toggle::make('show_teacher_code')
                                ->label('Tampilkan Kode Guru')
                                ->default(true),
                        ]),
                ])
                ->action(function (array $data) {
                    $url = route('reports.schedule', [
                        'study_group_id' => $data['print_mode'] === 'global' ? 'all' : $data['study_group_id'],
                        'show_subject_code' => $data['show_subject_code'] ? 1 : 0,
                        'show_teacher_code' => $data['show_teacher_code'] ? 1 : 0,
                        'paper_size' => $data['paper_size'] ?? 'a4',
                        'orientation' => $data['orientation'] ?? 'landscape',
                    ]);
                    
                    return redirect($url);
                }),
            CreateAction::make(),
        ];
    }

    protected function autoGenerateSchedule(array $data): void
    {
        foreach ($data['study_group_ids'] as $rombelId) {
            $studyGroup = \App\Models\StudyGroup::with('academicYear')->find($rombelId);
            if (!$studyGroup) continue;

            if ($data['overwrite']) {
                \App\Models\Schedule::where('study_group_id', $rombelId)->delete();
            }

            $allSlots = \App\Models\TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $studyGroup->level_id))
                ->orderBy('urutan')
                ->get();

            // Ambil Konfigurasi Hari/Aturan
            $dayConfigs = \App\Models\DayConfig::with(['maxTimeSlot'])
                ->where('academic_year_id', $studyGroup->academic_year_id)
                ->whereJsonContains('level_ids', (int) $studyGroup->level_id)
                ->get();

        $subjectsToProcess = $this->prepareSubjectsToProcess($studyGroup, $rombelId);

        // Prioritas 1: Aturan Wajib dari Database (misal Upacara)
        $this->applyMandatoryRules($subjectsToProcess, $studyGroup, $rombelId, $dayConfigs, $allSlots);

        // Prioritas 2: Sisa Mapel (dengan menghormati batas jam)
        $this->distributeRemainingSubjects($subjectsToProcess, $studyGroup, $rombelId, $dayConfigs, $allSlots);
        }

        \Filament\Notifications\Notification::make()
            ->title('Jadwal Berhasil di-Generate Otomatis!')
            ->success()
            ->send();
    }

    protected function applyMandatoryRules(array &$subjectsToProcess, $studyGroup, int $rombelId, $dayConfigs, $allSlots): void
    {
        foreach ($dayConfigs as $config) {
            if (!$config->mandatory_subject_id || !$config->mandatory_time_slot_id) continue;

            $itemIdx = collect($subjectsToProcess)->search(fn($i) => $i['model']->id == $config->mandatory_subject_id);
            if ($itemIdx === false) continue;

            $item = &$subjectsToProcess[$itemIdx];
            if ($item['remaining'] <= 0) continue;

            // Cek bentrok
            $isBusy = $this->isSlotBusy($config->day, $config->mandatory_time_slot_id, $rombelId, $item['teacher_id']);
            
            if (!$isBusy) {
                \App\Models\Schedule::create([
                    'study_group_id' => $rombelId,
                    'subject_id' => $item['model']->id,
                    'teacher_id' => $item['teacher_id'],
                    'hari' => $config->day,
                    'start_time_slot_id' => $config->mandatory_time_slot_id,
                    'end_time_slot_id' => $config->mandatory_time_slot_id,
                ]);
                $item['remaining']--;
            }
        }


    }

    protected function prepareSubjectsToProcess(\App\Models\StudyGroup $studyGroup, int $rombelId): array
    {
        $subjects = \App\Models\Subject::where(function($q) use ($studyGroup) {
            $q->whereHas('levels', fn($sq) => $sq->where('levels.id', $studyGroup->level_id))
              ->orDoesntHave('levels');
        })->get();

        $toProcess = [];
        foreach ($subjects as $subject) {
            $teacherId = $subject->is_umum 
                ? $studyGroup->walikelas_id 
                : \App\Models\Teacher::whereHas('subjects', fn($q) => $q->where('subjects.id', $subject->id))
                    ->where('status', 'aktif')->first()?->id;

            if (!$teacherId) continue;

            $usedJp = \App\Models\Schedule::where('study_group_id', $rombelId)
                ->where('subject_id', $subject->id)->get()
                ->sum(function($s) {
                    $start = \App\Models\TimeSlot::find($s->start_time_slot_id);
                    $end = \App\Models\TimeSlot::find($s->end_time_slot_id);
                    return ($start && $end) ? abs($end->urutan - $start->urutan) + 1 : 0;
                });
            
            $remaining = $subject->total_jp - $usedJp;
            if ($remaining > 0) {
                $isSpecialist = $teacherId != $studyGroup->walikelas_id;

                $toProcess[] = [
                    'model' => $subject,
                    'teacher_id' => $teacherId,
                    'remaining' => $remaining,
                    'is_one_day_finish' => $subject->is_one_day_finish,
                    'priority' => $subject->scheduling_priority,
                    'is_specialist' => $isSpecialist
                ];
            }
        }

        // PRIORITAS UTAMA:
        // 1. Specialist Teachers (Non-Wali Kelas) - Karena mereka paling rawan bentrok antar rombel
        // 2. Olahraga/PJOK - Karena butuh slot panjang
        // 3. Sisa JP Terbanyak - Agar mapel besar cepat habis
        usort($toProcess, function($a, $b) {
            // 1. Prioritas (User defined: 3 > 2 > 1)
            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] <=> $a['priority'];
            }

            // 2. Guru Spesialis (Agar tidak bentrok antar rombel)
            if ($a['is_specialist'] && !$b['is_specialist']) return -1;
            if (!$a['is_specialist'] && $b['is_specialist']) return 1;
            
            // 3. One Day Finish (seperti PJOK)
            if ($a['is_one_day_finish'] && !$b['is_one_day_finish']) return -1;
            if (!$a['is_one_day_finish'] && $b['is_one_day_finish']) return 1;
            
            // 4. Sisa JP terbanyak
            return $b['remaining'] <=> $a['remaining'];
        });

        return $toProcess;
    }



    protected function distributeRemainingSubjects(array &$subjectsToProcess, \App\Models\StudyGroup $studyGroup, int $rombelId, $dayConfigs, $allSlots): void
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        \Illuminate\Support\Facades\Log::info("Distributing subjects for rombel $rombelId. Total to process: " . count($subjectsToProcess) . ". Slots found: " . count($allSlots));
        
        // TAHAP 1: Jadwalkan Mapel Blok (is_one_day_finish) terlebih dahulu
        foreach ($subjectsToProcess as &$item) {
            if (!$item['is_one_day_finish'] || $item['remaining'] <= 0) continue;

            foreach ($days as $day) {
                if ($item['remaining'] <= 0) break;

                $config = $dayConfigs->where('day', $day)->first();
                $maxUrutan = ($config && $config->maxTimeSlot) ? $config->maxTimeSlot->urutan : 999;
                
                // \Illuminate\Support\Facades\Log::info("Trying subject {$item['model']->nama_mapel} on $day. Max Urutan: $maxUrutan");

                $config = $dayConfigs->where('day', $day)->first();
                if ($config && $config->is_closed) continue;
                $maxUrutan = ($config && $config->maxTimeSlot) ? $config->maxTimeSlot->urutan : 999;

                foreach ($allSlots as $slot) {
                    if ($item['remaining'] <= 0) break;
                    if ($slot->is_istirahat || $slot->urutan > $maxUrutan) continue;
                    if ($this->isSlotBusy($day, $slot->id, $rombelId)) continue;

                    $duration = $item['remaining']; 
                    $availableSlots = [];
                    $offset = 0;
                    
                    while (count($availableSlots) < $duration) {
                        $nextSlot = $allSlots->where('urutan', $slot->urutan + $offset)->first();
                        $offset++;

                        if (!$nextSlot || $nextSlot->urutan > $maxUrutan) break;
                        
                        if ($nextSlot->is_istirahat) {
                            continue; // Skip istirahat tapi lanjut cari slot
                        }

                        $isBusy = $this->isSlotBusy($day, $nextSlot->id, $rombelId, $item['teacher_id']);
                        if ($isBusy) {
                            // \Illuminate\Support\Facades\Log::info("Teacher {$item['teacher_id']} busy on $day slot {$nextSlot->urutan}");
                            break;
                        }
                        
                        $availableSlots[] = $nextSlot;
                    }

                    if (count($availableSlots) === $duration) {
                        \App\Models\Schedule::create([
                            'study_group_id' => $rombelId,
                            'subject_id' => $item['model']->id,
                            'teacher_id' => $item['teacher_id'],
                            'hari' => $day,
                            'start_time_slot_id' => $slot->id,
                            'end_time_slot_id' => $availableSlots[count($availableSlots) - 1]->id,
                        ]);
                        $item['remaining'] -= count($availableSlots);
                        break; // Lanjut ke hari berikutnya atau selesai
                    }
                }
            }
        }

        // TAHAP 2: Jadwalkan Sisa Mata Pelajaran (Dua Sub-Tahap)
        // Sub-Tahap A: Guru Spesialis (Paling rawan bentrok)
        // Sub-Tahap B: Wali Kelas (Paling fleksibel)
        foreach (['specialist', 'walikelas'] as $priorityGroup) {
            foreach ($days as $day) {
                $config = $dayConfigs->where('day', $day)->first();
                if ($config && $config->is_closed) continue;
                $maxUrutan = ($config && $config->maxTimeSlot) ? $config->maxTimeSlot->urutan : 999;

                $subjectsScheduledToday = [];

                foreach ($allSlots as $slot) {
                    if ($slot->is_istirahat || $slot->urutan > $maxUrutan) continue;
                    if ($this->isSlotBusy($day, $slot->id, $rombelId)) continue;

                    // Urutkan sisa mapel berdasarkan beban JP terbanyak
                    usort($subjectsToProcess, fn($a, $b) => $b['remaining'] <=> $a['remaining']);

                    $scheduled = false;
                    foreach ([3, 2, 1] as $targetDuration) {
                        if ($scheduled) break;

                        foreach ($subjectsToProcess as &$item) {
                            if ($item['remaining'] <= 0) continue;
                            if ($item['remaining'] < $targetDuration) continue;
                            
                            // Filter berdasarkan grup prioritas saat ini
                            if ($priorityGroup === 'specialist' && !$item['is_specialist']) continue;
                            if ($priorityGroup === 'walikelas' && $item['is_specialist']) continue;

                            if (in_array($item['model']->id, $subjectsScheduledToday)) continue;

                            $availableSlots = [];
                            $offset = 0;
                            while (count($availableSlots) < $targetDuration) {
                                $nextSlot = $allSlots->where('urutan', $slot->urutan + $offset)->first();
                                $offset++;

                                if (!$nextSlot || $nextSlot->urutan > $maxUrutan) break;
                                if ($nextSlot->is_istirahat) continue;

                                if ($this->isSlotBusy($day, $nextSlot->id, $rombelId, $item['teacher_id'])) break;
                                
                                $availableSlots[] = $nextSlot;
                            }

                            if (count($availableSlots) === $targetDuration) {
                                \App\Models\Schedule::create([
                                    'study_group_id' => $rombelId,
                                    'subject_id' => $item['model']->id,
                                    'teacher_id' => $item['teacher_id'],
                                    'hari' => $day,
                                    'start_time_slot_id' => $slot->id,
                                    'end_time_slot_id' => $availableSlots[count($availableSlots) - 1]->id,
                                ]);
                                $item['remaining'] -= count($availableSlots);
                                $subjectsScheduledToday[] = $item['model']->id;
                                $scheduled = true;
                                break;
                            }
                        }
                    }
                    if ($scheduled) continue; // Cek slot berikutnya
                }
            }
        }
    }

    protected function isSlotBusy(string $day, int $slotId, int $rombelId, ?int $teacherId = null): bool
    {
        $slot = \App\Models\TimeSlot::find($slotId);
        if (!$slot) return false;

        $busyRombel = \App\Models\Schedule::where('hari', $day)
            ->where(function($q) use ($slot) {
                $q->whereHas('startTimeSlot', fn($sq) => $sq->where('urutan', '<=', $slot->urutan))
                  ->whereHas('endTimeSlot', fn($sq) => $sq->where('urutan', '>=', $slot->urutan));
            })
            ->where('study_group_id', $rombelId)
            ->exists();

        if ($busyRombel) return true;

        if ($teacherId) {
            $busyTeacher = \App\Models\Schedule::where('hari', $day)
                ->where(function($q) use ($slot) {
                    $q->whereHas('startTimeSlot', fn($sq) => $sq->where('urutan', '<=', $slot->urutan))
                      ->whereHas('endTimeSlot', fn($sq) => $sq->where('urutan', '>=', $slot->urutan));
                })
                ->where('teacher_id', $teacherId)
                ->exists();
            
            if ($busyTeacher) return true;
        }

        return false;
    }
}
