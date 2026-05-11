<?php

namespace App\Filament\Widgets;

use App\Models\StudyGroup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class WaliKelasOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Only show for Teachers
        if (!$user->hasRole('guru')) {
            return [];
        }

        $studyGroup = StudyGroup::where('walikelas_id', $user->teacher->id ?? 0)
            ->withCount('students')
            ->first();

        if (!$studyGroup) {
            return [
                Stat::make('Status Wali Kelas', 'Belum Diatur')
                    ->description('Anda belum ditugaskan sebagai wali kelas di rombel manapun.')
                    ->color('gray'),
            ];
        }

        return [
            Stat::make('Rombel Anda', $studyGroup->nama_rombel)
                ->description($studyGroup->academicYear->tahun_ajaran ?? 'Tahun Ajaran Aktif')
                ->icon('heroicon-m-user-group')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/rombel/{$studyGroup->id}/edit'",
                ]),
            Stat::make('Total Siswa', $studyGroup->students_count)
                ->description('Klik untuk lihat daftar siswa')
                ->icon('heroicon-m-users')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/rombel/{$studyGroup->id}/edit'",
                ]),
            Stat::make('Presensi Hari Ini', 'Klik Disini')
                ->description('Mulai input presensi harian')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/rombel/{$studyGroup->id}/edit'",
                ]),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('guru');
    }
}
