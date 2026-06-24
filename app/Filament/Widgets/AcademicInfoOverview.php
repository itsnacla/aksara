<?php

namespace App\Filament\Widgets;

use App\Models\AcademicYear;
use App\Models\StudyGroup;
use App\Models\Schedule;
use App\Models\StudentLeave;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AcademicInfoOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $isGuru = $user?->hasRole('guru');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $totalRombel = StudyGroup::query()
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        $totalSchedules = Schedule::count();

        $pendingLeaves = StudentLeave::where('status', 'pending')->count();

        if ($isGuru) {
            $teacher = $user->teacher;
            if (!$teacher) {
                return [
                    Stat::make('Info Akademik', 'Data tidak tersedia')
                        ->description('Hubungi admin untuk pengaturan.')
                        ->color('gray'),
                ];
            }

            $todayName = Carbon::now()->locale('id')->dayName;
            $dayMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $todayIndo = $dayMap[$todayName] ?? $todayName;

            $todaySchedules = Schedule::where('teacher_id', $teacher->id)
                ->where('hari', $todayIndo)
                ->with(['studyGroup', 'subject', 'startTimeSlot', 'endTimeSlot'])
                ->get()
                ->sortBy(fn($s) => $s->startTimeSlot?->urutan ?? 999);

            $totalSubjects = Schedule::where('teacher_id', $teacher->id)
                ->distinct('subject_id')
                ->count('subject_id');

            $scheduleSummary = $todaySchedules->map(function ($s) {
                $start = $s->startTimeSlot?->start_time ?? '-';
                $end = $s->endTimeSlot?->end_time ?? '-';
                return "{$s->subject?->nama_mapel} ({$s->studyGroup?->nama_rombel}) {$start}-{$end}";
            })->join(', ');

            $jadwalCount = $todaySchedules->count();
            $jadwalLabel = $jadwalCount > 0 ? "{$jadwalCount} sesi hari ini" : 'Tidak ada jadwal hari ini';

            return [
                Stat::make('Tahun Ajaran Aktif', $activeYear ? $activeYear->tahun_ajaran : 'Belum Diatur')
                    ->description($activeYear ? 'Semester ' . $activeYear->semester : 'Silakan atur tahun ajaran')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary'),

                Stat::make('Jadwal Hari Ini', $jadwalLabel)
                    ->description($scheduleSummary ?: 'Cek jadwal lengkap di menu Penjadwalan')
                    ->descriptionIcon('heroicon-m-clock')
                    ->icon('heroicon-o-clock')
                    ->color('info'),

                Stat::make('Pengajuan Izin', $pendingLeaves)
                    ->description('Menunggu persetujuan')
                    ->descriptionIcon('heroicon-m-document-check')
                    ->icon('heroicon-o-document-check')
                    ->color($pendingLeaves > 0 ? 'warning' : 'success'),

                Stat::make('Total Mapel Diajar', $totalSubjects)
                    ->description('Mata pelajaran yang diampu')
                    ->descriptionIcon('heroicon-m-book-open')
                    ->icon('heroicon-o-book-open')
                    ->color('success'),
            ];
        }

        return [
            Stat::make('Tahun Ajaran Aktif', $activeYear ? $activeYear->tahun_ajaran : 'Belum Diatur')
                ->description($activeYear ? 'Semester ' . $activeYear->semester : 'Silakan atur tahun ajaran')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('Total Rombel', $totalRombel)
                ->description('Rombongan belajar aktif')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->icon('heroicon-o-rectangle-group')
                ->color('success'),

            Stat::make('Total Jadwal', $totalSchedules)
                ->description('Sesi pelajaran terjadwal')
                ->descriptionIcon('heroicon-m-clock')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Pengajuan Izin', $pendingLeaves)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-document-check')
                ->icon('heroicon-o-document-check')
                ->color($pendingLeaves > 0 ? 'warning' : 'success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'guru', 'staff']) ?? false;
    }
}
