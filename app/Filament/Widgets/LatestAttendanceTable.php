<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Widgets\Concerns\ScopesToTeacherStudents;
use App\Models\Attendance;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Livewire\Attributes\On;

class LatestAttendanceTable extends BaseWidget
{
    use ScopesToTeacherStudents;

    #[On('echo:attendance,AttendanceLogged')]
    public function refreshAttendance($event)
    {
        $isGuru = auth()->user()?->hasRole('guru');

        if ($isGuru) {
            $studentIds = $this->getTeacherStudentIds();
            if (! in_array($event['studentId'] ?? null, $studentIds)) {
                return;
            }
        }

        Notification::make()
            ->title('Presensi Masuk!')
            ->body("{$event['studentName']} baru saja dicatat sebagai {$event['status']}.")
            ->success()
            ->send();

        $this->dispatch('refreshTable');
    }

    protected static ?string $heading = 'Presensi Terbaru';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        $query = Attendance::query()->with(['student.user', 'studyGroup'])->latest('tanggal')->latest('created_at');

        if (auth()->user()?->hasRole('guru')) {
            $this->scopeTeacherAttendance($query);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hadir' => 'Hadir',
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        'alfa', 'alpha' => 'Alfa',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'sakit' => 'warning',
                        'izin' => 'info',
                        'alfa', 'alpha' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'hadir' => 'heroicon-m-check-circle',
                        'sakit' => 'heroicon-m-heart',
                        'izin' => 'heroicon-m-document-text',
                        'alfa', 'alpha' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                TextColumn::make('check_in')
                    ->label('Masuk')
                    ->time('H:i')
                    ->placeholder('—')
                    ->color('success'),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->recordUrl(fn ($record) => AttendanceResource::getUrl('index', [
                'tableAction' => 'edit',
                'tableActionRecord' => $record->id,
            ]))
            ->emptyStateHeading('Belum ada data presensi')
            ->emptyStateDescription('Data presensi akan muncul setelah input dilakukan.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'guru', 'staff']) ?? false;
    }
}
