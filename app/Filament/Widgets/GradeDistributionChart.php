<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToTeacherStudents;
use App\Models\AcademicYear;
use App\Models\Student;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class GradeDistributionChart extends BaseWidget
{
    use ScopesToTeacherStudents;

    protected static ?string $heading = 'Nilai Rata-rata Siswa';

    protected static ?string $description = 'Klik baris untuk melihat detail nilai';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $isGuru = auth()->user()?->hasRole('guru');

        $query = Student::query()
            ->with(['user', 'studyGroups'])
            ->whereHas('grades', function ($q) use ($activeYear) {
                $q->when($activeYear, fn ($sq) => $sq->where('academic_year_id', $activeYear->id));
            });

        if ($isGuru) {
            $studentIds = $this->getTeacherStudentIds();
            if (empty($studentIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $studentIds);
            }
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                TextColumn::make('rombel')
                    ->label('Rombel')
                    ->badge()
                    ->color('gray')
                    ->state(function ($record) {
                        $sg = $record->studyGroups->first();

                        return $sg?->nama_rombel ?? '-';
                    }),

                TextColumn::make('rata_rata')
                    ->label('Rata-rata')
                    ->state(function ($record) use ($activeYear) {
                        $grades = $record->grades()
                            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                            ->get();
                        if ($grades->isEmpty()) {
                            return '-';
                        }

                        return round($grades->avg(function ($g) {
                            return ($g->nilai_tugas + $g->nilai_uts + $g->nilai_uas) / 3;
                        }), 1);
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        $state >= 60 => 'orange',
                        default => 'danger',
                    }),

                TextColumn::make('grade')
                    ->label('Grade')
                    ->state(function ($record) use ($activeYear) {
                        $grades = $record->grades()
                            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                            ->get();
                        if ($grades->isEmpty()) {
                            return '-';
                        }
                        $avg = $grades->avg(function ($g) {
                            return ($g->nilai_tugas + $g->nilai_uts + $g->nilai_uas) / 3;
                        });

                        return match (true) {
                            $avg >= 90 => 'A',
                            $avg >= 80 => 'B',
                            $avg >= 70 => 'C',
                            $avg >= 60 => 'D',
                            default => 'E',
                        };
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'orange',
                        'E' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Belum ada data nilai')
            ->emptyStateDescription('Nilai siswa akan muncul setelah input dilakukan.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'guru']) ?? false;
    }
}
