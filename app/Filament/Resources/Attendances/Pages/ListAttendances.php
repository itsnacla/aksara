<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\Schedule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan_qr')
                ->label('Scan QR')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->url(route('scan-presensi'))
                ->openUrlInNewTab(),
            ExportAction::make()
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename('Data_Presensi_' . date('Y-m-d'))
                ]),
            Action::make('cetak_laporan')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(function (ListRecords $livewire) {
                    $filters = $livewire->tableFilters;
                    $query = http_build_query([
                        'study_group_id' => $filters['rombel_filter']['study_group_id'] ?? null,
                        'from' => $filters['tanggal']['from'] ?? null,
                        'until' => $filters['tanggal']['until'] ?? null,
                    ]);
                    return route('reports.attendance') . '?' . $query;
                })
                ->openUrlInNewTab(),
            Action::make('batch_input')
                ->label('Batch Input')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->modalHeading('Batch Input Presensi Siswa')
                ->modalWidth('4xl')
                ->form([
                    Section::make('Filter Rombel & Tanggal')
                        ->schema([
                            DatePicker::make('tanggal')
                                ->label('Tanggal Presensi')
                                ->default(now())
                                ->required()
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::loadStudentsForModal($get, $set)),
                            Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(function () {
                                    $query = StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true));
                                    $user = auth()->user();
                                    
                                    if ($user && $user->hasRole('guru') && $user->teacher) {
                                        $waliKelasId = $user->teacher->id;
                                        $scheduledRombelIds = Schedule::where('teacher_id', $waliKelasId)->pluck('study_group_id')->toArray();
                                        
                                        $query->where(function($q) use ($waliKelasId, $scheduledRombelIds) {
                                            $q->where('walikelas_id', $waliKelasId)
                                              ->orWhereIn('id', $scheduledRombelIds);
                                        });
                                    }
                                    
                                    return $query->pluck('nama_rombel', 'id');
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::loadStudentsForModal($get, $set)),
                        ])->columns(2),
                    
                    Section::make('Daftar Siswa')
                        ->schema([
                            Repeater::make('items')
                                ->label('')
                                ->schema([
                                    Hidden::make('student_id'),
                                    TextInput::make('student_name')
                                        ->label('Nama Siswa')
                                        ->disabled()
                                        ->dehydrated(false),
                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'hadir' => 'Hadir',
                                            'sakit' => 'Sakit',
                                            'izin' => 'Izin',
                                            'alpha' => 'Alpha',
                                        ])
                                        ->required()
                                        ->default('hadir'),
                                    TextInput::make('catatan')
                                        ->label('Catatan')
                                        ->placeholder('Opsional...'),
                                ])
                                ->columns(3)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false),
                        ])
                        ->visible(fn (Get $get) => filled($get('study_group_id'))),
                ])
                ->action(function (array $data): void {
                    $delay = 0;
                    foreach ($data['items'] as $item) {
                        $attendance = Attendance::updateOrCreate(
                            [
                                'student_id' => $item['student_id'],
                                'study_group_id' => $data['study_group_id'],
                                'tanggal' => $data['tanggal'],
                            ],
                            [
                                'status' => $item['status'],
                                'catatan' => $item['catatan'],
                            ]
                        );

                        // Send WA Notification for manual batch input
                        if (!$attendance->wa_sent_at) {
                            \App\Jobs\SendWhatsAppAttendanceNotification::dispatch($attendance)
                                ->delay(now()->addSeconds($delay));
                            
                            $delay += 2; // Progressive delay for batch
                        }
                    }

                    Notification::make()
                        ->title('Batch input berhasil disimpan')
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->label('Input Manual (Satuan)'),
        ];
    }

    public static function loadStudentsForModal(Get $get, Set $set): void
    {
        $studyGroupId = $get('study_group_id');
        $tanggal = $get('tanggal');

        if (!$studyGroupId || !$tanggal) {
            $set('items', []);
            return;
        }

        $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
            ->with('user')
            ->get();

        $existing = Attendance::where('study_group_id', $studyGroupId)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('student_id');

        $items = $students->map(fn ($student) => [
            'student_id' => $student->id,
            'student_name' => $student->user->name,
            'status' => $existing[$student->id]->status ?? 'hadir',
            'catatan' => $existing[$student->id]->catatan ?? null,
        ])->toArray();

        $set('items', $items);
    }
}
