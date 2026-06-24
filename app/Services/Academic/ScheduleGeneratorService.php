<?php

namespace App\Services\Academic;

use App\Models\DayConfig;
use App\Models\Schedule;
use App\Models\StudyGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;

class ScheduleGeneratorService
{
    /**
     * Generate jadwal otomatis untuk satu atau lebih rombel.
     *
     * @param  array<int>  $studyGroupIds
     */
    public function generate(array $studyGroupIds, bool $overwrite = false): void
    {
        foreach ($studyGroupIds as $rombelId) {
            $studyGroup = StudyGroup::with('academicYear')->find($rombelId);
            if (! $studyGroup) {
                continue;
            }

            if ($overwrite) {
                Schedule::where('study_group_id', $rombelId)->delete();
            }

            $allSlots = TimeSlot::whereHas('levels', fn ($q) => $q->where('levels.id', $studyGroup->level_id))
                ->orderBy('urutan')
                ->get();

            $dayConfigs = DayConfig::with(['maxTimeSlot'])
                ->where('academic_year_id', $studyGroup->academic_year_id)
                ->whereJsonContains('level_ids', (int) $studyGroup->level_id)
                ->get();

            $subjectsToProcess = $this->prepareSubjectsToProcess($studyGroup, $rombelId);

            // Prioritas 1: Aturan Wajib dari Database (misal Upacara)
            $this->applyMandatoryRules($subjectsToProcess, $studyGroup, $rombelId, $dayConfigs, $allSlots);

            // Prioritas 2: Sisa Mapel (dengan menghormati batas jam)
            $this->distributeRemainingSubjects($subjectsToProcess, $studyGroup, $rombelId, $dayConfigs, $allSlots);
        }
    }

    protected function prepareSubjectsToProcess(StudyGroup $studyGroup, int $rombelId): array
    {
        $subjects = Subject::where(function ($q) use ($studyGroup) {
            $q->whereHas('levels', fn ($sq) => $sq->where('levels.id', $studyGroup->level_id))
              ->orDoesntHave('levels');
        })->get();

        $toProcess = [];
        foreach ($subjects as $subject) {
            $walikelas = Teacher::find($studyGroup->walikelas_id);
            $isActiveWalikelas = $walikelas && $walikelas->status === 'aktif';

            $teacherId = $subject->is_umum && $isActiveWalikelas
                ? $studyGroup->walikelas_id
                : Teacher::whereHas('subjects', fn ($q) => $q->where('subjects.id', $subject->id))
                    ->where('status', 'aktif')->first()?->id;

            if (! $teacherId) {
                continue;
            }

            $usedJp = Schedule::where('study_group_id', $rombelId)
                ->where('subject_id', $subject->id)->get()
                ->sum(function ($s) {
                    $start = TimeSlot::find($s->start_time_slot_id);
                    $end   = TimeSlot::find($s->end_time_slot_id);

                    return ($start && $end) ? abs($end->urutan - $start->urutan) + 1 : 0;
                });

            $remaining = $subject->total_jp - $usedJp;
            if ($remaining > 0) {
                $isSpecialist = $teacherId != $studyGroup->walikelas_id;

                $toProcess[] = [
                    'model'           => $subject,
                    'teacher_id'      => $teacherId,
                    'remaining'       => $remaining,
                    'is_one_day_finish' => $subject->is_one_day_finish,
                    'priority'        => $subject->scheduling_priority,
                    'is_specialist'   => $isSpecialist,
                ];
            }
        }

        // PRIORITAS UTAMA:
        // 1. Specialist Teachers (Non-Wali Kelas) — paling rawan bentrok antar rombel
        // 2. One Day Finish (misal PJOK) — butuh slot panjang
        // 3. Sisa JP terbanyak — agar mapel besar cepat habis
        usort($toProcess, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] <=> $a['priority'];
            }
            if ($a['is_specialist'] && ! $b['is_specialist']) {
                return -1;
            }
            if (! $a['is_specialist'] && $b['is_specialist']) {
                return 1;
            }
            if ($a['is_one_day_finish'] && ! $b['is_one_day_finish']) {
                return -1;
            }
            if (! $a['is_one_day_finish'] && $b['is_one_day_finish']) {
                return 1;
            }

            return $b['remaining'] <=> $a['remaining'];
        });

        return $toProcess;
    }

    protected function applyMandatoryRules(array &$subjectsToProcess, StudyGroup $studyGroup, int $rombelId, $dayConfigs, $allSlots): void
    {
        foreach ($dayConfigs as $config) {
            if (! $config->mandatory_subject_id || ! $config->mandatory_time_slot_id) {
                continue;
            }

            $itemIdx = collect($subjectsToProcess)->search(fn ($i) => $i['model']->id == $config->mandatory_subject_id);
            if ($itemIdx === false) {
                continue;
            }

            $item = &$subjectsToProcess[$itemIdx];
            if ($item['remaining'] <= 0) {
                continue;
            }

            $isBusy = $this->isSlotBusy($config->day, $config->mandatory_time_slot_id, $rombelId, $item['teacher_id'], $item['model']->id);

            if (! $isBusy) {
                Schedule::create([
                    'study_group_id'     => $rombelId,
                    'subject_id'         => $item['model']->id,
                    'teacher_id'         => $item['teacher_id'],
                    'hari'               => $config->day,
                    'start_time_slot_id' => $config->mandatory_time_slot_id,
                    'end_time_slot_id'   => $config->mandatory_time_slot_id,
                ]);
                $item['remaining']--;
            }
        }
    }

    protected function distributeRemainingSubjects(array &$subjectsToProcess, StudyGroup $studyGroup, int $rombelId, $dayConfigs, $allSlots): void
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // TAHAP 1: Jadwalkan Mapel Blok (is_one_day_finish)
        foreach ($subjectsToProcess as &$item) {
            if (! $item['is_one_day_finish'] || $item['remaining'] <= 0) {
                continue;
            }

            foreach ($days as $day) {
                if ($item['remaining'] <= 0) {
                    break;
                }

                $config = $dayConfigs->where('day', $day)->first();
                if ($config && $config->is_closed) {
                    continue;
                }
                $maxUrutan = ($config && $config->maxTimeSlot) ? $config->maxTimeSlot->urutan : 999;

                foreach ($allSlots as $slot) {
                    if ($item['remaining'] <= 0) {
                        break;
                    }
                    if ($slot->is_istirahat || $slot->urutan > $maxUrutan) {
                        continue;
                    }
                    if ($this->isSlotBusy($day, $slot->id, $rombelId, null, $item['model']->id)) {
                        continue;
                    }

                    $duration       = $item['remaining'];
                    $availableSlots = [];
                    $offset         = 0;

                    while (count($availableSlots) < $duration) {
                        $nextSlot = $allSlots->where('urutan', $slot->urutan + $offset)->first();
                        $offset++;

                        if (! $nextSlot || $nextSlot->urutan > $maxUrutan) {
                            break;
                        }
                        if ($nextSlot->is_istirahat) {
                            continue;
                        }
                        if ($this->isSlotBusy($day, $nextSlot->id, $rombelId, $item['teacher_id'], $item['model']->id)) {
                            break;
                        }

                        $availableSlots[] = $nextSlot;
                    }

                    if (count($availableSlots) === $duration) {
                        Schedule::create([
                            'study_group_id'     => $rombelId,
                            'subject_id'         => $item['model']->id,
                            'teacher_id'         => $item['teacher_id'],
                            'hari'               => $day,
                            'start_time_slot_id' => $slot->id,
                            'end_time_slot_id'   => $availableSlots[count($availableSlots) - 1]->id,
                        ]);
                        $item['remaining'] -= count($availableSlots);
                        break;
                    }
                }
            }
        }

        // TAHAP 2: Sisa Mapel — Sub-Tahap A: Specialist, Sub-Tahap B: Walikelas
        foreach (['specialist', 'walikelas'] as $priorityGroup) {
            foreach ($days as $day) {
                $config = $dayConfigs->where('day', $day)->first();
                if ($config && $config->is_closed) {
                    continue;
                }
                $maxUrutan = ($config && $config->maxTimeSlot) ? $config->maxTimeSlot->urutan : 999;

                $subjectsScheduledToday = [];

                foreach ($allSlots as $slot) {
                    if ($slot->is_istirahat || $slot->urutan > $maxUrutan) {
                        continue;
                    }
                    // We don't have $item yet here, so just pass null for subjectId
                    // But wait, the first loop slot check doesn't know which subject.
                    // It's okay, we will re-check it inside the loop when we have $item.
                    if ($this->isSlotBusy($day, $slot->id, $rombelId)) {
                        // We still do a quick check, but it might block religions if another religion is there.
                        // So we should remove this early check and let the inner loop handle it!
                    }

                    usort($subjectsToProcess, fn ($a, $b) => $b['remaining'] <=> $a['remaining']);

                    $scheduled = false;
                    foreach ([3, 2, 1] as $targetDuration) {
                        if ($scheduled) {
                            break;
                        }

                        foreach ($subjectsToProcess as &$item) {
                            if ($item['remaining'] <= 0) {
                                continue;
                            }
                            if ($item['remaining'] < $targetDuration) {
                                continue;
                            }
                            if ($priorityGroup === 'specialist' && ! $item['is_specialist']) {
                                continue;
                            }
                            if ($priorityGroup === 'walikelas' && $item['is_specialist']) {
                                continue;
                            }
                            if (in_array($item['model']->id, $subjectsScheduledToday)) {
                                continue;
                            }

                            $availableSlots = [];
                            $offset         = 0;
                            while (count($availableSlots) < $targetDuration) {
                                $nextSlot = $allSlots->where('urutan', $slot->urutan + $offset)->first();
                                $offset++;

                                if (! $nextSlot || $nextSlot->urutan > $maxUrutan) {
                                    break;
                                }
                                if ($nextSlot->is_istirahat) {
                                    continue;
                                }
                                if ($this->isSlotBusy($day, $nextSlot->id, $rombelId, $item['teacher_id'], $item['model']->id)) {
                                    break;
                                }

                                $availableSlots[] = $nextSlot;
                            }

                            if (count($availableSlots) === $targetDuration) {
                                Schedule::create([
                                    'study_group_id'     => $rombelId,
                                    'subject_id'         => $item['model']->id,
                                    'teacher_id'         => $item['teacher_id'],
                                    'hari'               => $day,
                                    'start_time_slot_id' => $slot->id,
                                    'end_time_slot_id'   => $availableSlots[count($availableSlots) - 1]->id,
                                ]);
                                $item['remaining'] -= count($availableSlots);
                                $subjectsScheduledToday[] = $item['model']->id;
                                $scheduled = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    protected function isSlotBusy(string $day, int $slotId, int $rombelId, ?int $teacherId = null, ?int $subjectId = null): bool
    {
        $slot = TimeSlot::find($slotId);
        if (! $slot) {
            return false;
        }

        $busySchedules = Schedule::with('subject')->where('hari', $day)
            ->where(function ($q) use ($slot) {
                $q->whereHas('startTimeSlot', fn ($sq) => $sq->where('urutan', '<=', $slot->urutan))
                  ->whereHas('endTimeSlot', fn ($sq) => $sq->where('urutan', '>=', $slot->urutan));
            })
            ->where('study_group_id', $rombelId)
            ->get();

        if ($busySchedules->isNotEmpty()) {
            $canOverlap = false;

            // Jika subject yang mau dijadwalkan adalah Pendidikan Agama
            if ($subjectId) {
                $subject = Subject::find($subjectId);
                if ($subject && str_contains(strtolower($subject->nama_mapel), 'pendidikan agama')) {
                    // Pastikan jadwal yang bentrok juga adalah Pendidikan Agama
                    $allBusyAreReligions = $busySchedules->every(function ($sch) {
                        return str_contains(strtolower($sch->subject->nama_mapel), 'pendidikan agama');
                    });
                    
                    if ($allBusyAreReligions) {
                        $canOverlap = true;
                    }
                }
            }

            if (! $canOverlap) {
                return true;
            }
        }

        if ($teacherId) {
            $busyTeacher = Schedule::where('hari', $day)
                ->where(function ($q) use ($slot) {
                    $q->whereHas('startTimeSlot', fn ($sq) => $sq->where('urutan', '<=', $slot->urutan))
                      ->whereHas('endTimeSlot', fn ($sq) => $sq->where('urutan', '>=', $slot->urutan));
                })
                ->where('teacher_id', $teacherId)
                ->exists();

            if ($busyTeacher) {
                return true;
            }
        }

        return false;
    }
}
