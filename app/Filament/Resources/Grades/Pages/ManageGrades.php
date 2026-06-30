<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ManageGrades extends ManageRecords
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getImportCsvAction(),
            $this->getBatchInputAction(),
            CreateAction::make()
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false),
        ];
    }

    protected function getImportCsvAction(): Action
    {
        return Action::make('import_csv')
            ->label('Import Nilai')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->modalHeading('Import Berkas Nilai')
            ->modalDescription(new HtmlString('Pilih Mata Pelajaran dan Rombel, lalu unggah berkas nilai. Harap pastikan TP (Tujuan Pembelajaran) sudah diatur sebelumnya.<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"><a href="'.route('download.template', ['type' => 'grade', 'format' => 'xlsx']).'" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #10b981; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xlsx)</a><a href="'.route('download.template', ['type' => 'grade', 'format' => 'xls']).'" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #059669; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xls)</a><a href="'.route('download.template', ['type' => 'grade', 'format' => 'csv']).'" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #4b5563; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template CSV</a></div>'))
            ->modalSubmitActionLabel('Mulai Proses Impor')
            ->modalWidth('7xl')
            ->form([
                Section::make('Filter Mata Pelajaran & Rombel')
                    ->schema([
                        Select::make('subject_id')
                            ->label('Pilih Mapel')
                            ->options(function () {
                                $user = auth()->user();
                                $query = Subject::query()->where('is_graded', true);
                                if ($user->hasRole('guru') && $user->teacher) {
                                    $teacherId = $user->teacher->id;
                                    $isWaliKelas = $user->teacher->is_walikelas;
                                    if ($isWaliKelas) {
                                        $query->where(function ($q) use ($teacherId) {
                                            $q->where('is_umum', true)
                                                ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                        });
                                    } else {
                                        $query->where(function ($q) use ($teacherId) {
                                            $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                        });
                                    }
                                }

                                return $query->pluck('nama_mapel', 'id');
                            })
                            ->required()
                            ->live(),
                        Select::make('study_group_id')
                            ->label('Pilih Rombel')
                            ->options(function (Get $get) {
                                $subjectId = $get('subject_id');
                                if (! $subjectId) {
                                    return [];
                                }
                                $user = auth()->user();
                                $activeYearId = AcademicYear::where('is_active', true)->value('id');
                                if (! $activeYearId) {
                                    return [];
                                }
                                $query = StudyGroup::query()->where('academic_year_id', $activeYearId);
                                if ($user->hasRole('guru') && $user->teacher) {
                                    $teacherId = $user->teacher->id;
                                    $isWaliKelas = $user->teacher->is_walikelas;
                                    if ($isWaliKelas) {
                                        $subject = Subject::find($subjectId);
                                        if ($subject && $subject->is_umum) {
                                            $query->where('walikelas_id', $teacherId);
                                        } else {
                                            $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                        }
                                    } else {
                                        $hasSchedules = Schedule::where('teacher_id', $teacherId)->where('subject_id', $subjectId)->exists();
                                        if ($hasSchedules) {
                                            $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                        }
                                    }
                                }

                                return $query->pluck('nama_rombel', 'id');
                            })
                            ->required()
                            ->live(),
                    ])->columns(2),
                Hidden::make('parsed_json')->default('[]'),
                FileUpload::make('file')
                    ->label('Pilih Berkas (CSV / Excel)')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! $state) {
                            $set('parsed_json', '[]');

                            return;
                        }
                        $file = is_array($state) ? reset($state) : $state;
                        $path = null;
                        $isTempDownloaded = false;
                        if (is_string($file)) {
                            if (file_exists($file)) {
                                $path = $file;
                            } elseif (Storage::exists($file)) {
                                $diskDriver = config('filesystems.disks.'.config('filesystems.default').'.driver');
                                if (in_array($diskDriver, ['local', 'public'])) {
                                    $path = Storage::path($file);
                                } else {
                                    $tmpPath = storage_path('app/livewire-tmp/'.basename($file));
                                    if (! file_exists(dirname($tmpPath))) {
                                        mkdir(dirname($tmpPath), 0755, true);
                                    }
                                    file_put_contents($tmpPath, Storage::get($file));
                                    $path = $tmpPath;
                                    $isTempDownloaded = true;
                                }
                            } else {
                                $tmpPath = storage_path('app/livewire-tmp/'.basename($file));
                                if (file_exists($tmpPath)) {
                                    $path = $tmpPath;
                                }
                            }
                        } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
                            $path = $file->getRealPath();
                        }
                        if (! $path || ! file_exists($path)) {
                            return;
                        }
                        try {
                            $spreadsheet = IOFactory::load($path);
                            $allRows = $spreadsheet->getActiveSheet()->toArray();
                        } catch (\Exception $e) {
                            if ($isTempDownloaded && file_exists($path)) {
                                @unlink($path);
                            }

                            return;
                        }
                        if ($isTempDownloaded && file_exists($path)) {
                            @unlink($path);
                        }
                        $rows = [];
                        foreach ($allRows as $r) {
                            if (array_filter($r, fn ($cell) => trim((string) $cell) !== '') !== []) {
                                $rows[] = $r;
                            }
                        }
                        if (count($rows) < 2) {
                            return;
                        }
                        $headers = array_map('trim', array_map('strtolower', array_map('strval', array_shift($rows))));
                        $required = ['nisn', 'nama_siswa', 'nilai_tugas', 'nilai_uts', 'nilai_uas'];
                        if (array_diff($required, $headers)) {
                            return;
                        }

                        $validRows = 0;
                        $previewRows = [];
                        foreach ($rows as $row) {
                            if (count($row) > count($headers)) {
                                $row = array_slice($row, 0, count($headers));
                            } else {
                                $row = array_pad($row, count($headers), '');
                            }
                            $data = array_combine($headers, $row);
                            if (trim($data['nisn'])) {
                                $validRows++;
                                if (count($previewRows) < 5) {
                                    $previewRows[] = [
                                        'nisn' => trim($data['nisn']),
                                        'nama_siswa' => trim($data['nama_siswa']),
                                        'nilai_tugas' => trim($data['nilai_tugas']),
                                        'nilai_uts' => trim($data['nilai_uts']),
                                        'nilai_uas' => trim($data['nilai_uas']),
                                    ];
                                }
                            }
                        }
                        $set('parsed_json', json_encode([
                            'valid_count' => $validRows,
                            'rows' => $previewRows,
                        ]));
                    }),
                TextEntry::make('preview')
                    ->label('Pratinjau Tabel Hasil Parsing')
                    ->state(function (callable $get) {
                        $json = $get('parsed_json');
                        $data = json_decode($json, true);
                        if (empty($data) || empty($data['rows'])) {
                            return new HtmlString('<div class="text-sm text-gray-400 italic p-4 border border-dashed rounded-lg text-center bg-gray-50 dark:bg-white/5">Pilih berkas untuk memuat tabel pratinjau otomatis...</div>');
                        }

                        return view('filament.components.import-preview-table', [
                            'type' => 'grade',
                            'rows' => $data['rows'],
                            'validCount' => $data['valid_count'] ?? 0,
                        ]);
                    }),
            ])
            ->action(function (array $data) {
                $activeYearId = AcademicYear::where('is_active', true)->value('id');
                $teacherId = auth()->user()->teacher?->id;
                $subjectId = $data['subject_id'];
                $studyGroupId = $data['study_group_id'];

                if (! $subjectId || ! $studyGroupId) {
                    Notification::make()->title('Harap pilih mapel dan rombel')->danger()->send();

                    return;
                }

                $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                $path = null;
                $isTempDownloaded = false;
                if (is_string($file)) {
                    if (file_exists($file)) {
                        $path = $file;
                    } elseif (Storage::exists($file)) {
                        $diskDriver = config('filesystems.disks.'.config('filesystems.default').'.driver');
                        if (in_array($diskDriver, ['local', 'public'])) {
                            $path = Storage::path($file);
                        } else {
                            $tmpPath = storage_path('app/livewire-tmp/'.basename($file));
                            if (! file_exists(dirname($tmpPath))) {
                                mkdir(dirname($tmpPath), 0755, true);
                            }
                            file_put_contents($tmpPath, Storage::get($file));
                            $path = $tmpPath;
                            $isTempDownloaded = true;
                        }
                    } else {
                        $tmpPath = storage_path('app/livewire-tmp/'.basename($file));
                        if (file_exists($tmpPath)) {
                            $path = $tmpPath;
                        }
                    }
                } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
                    $path = $file->getRealPath();
                }

                if (! $path || ! file_exists($path)) {
                    Notification::make()->title('Gagal membaca berkas')->danger()->send();

                    return;
                }

                try {
                    $spreadsheet = IOFactory::load($path);
                    $allRows = $spreadsheet->getActiveSheet()->toArray();
                } catch (\Exception $e) {
                    if ($isTempDownloaded && file_exists($path)) {
                        @unlink($path);
                    }
                    Notification::make()->title('Format berkas tidak didukung')->danger()->send();

                    return;
                }
                if ($isTempDownloaded && file_exists($path)) {
                    @unlink($path);
                }

                $rows = [];
                foreach ($allRows as $r) {
                    if (array_filter($r, fn ($cell) => trim((string) $cell) !== '') !== []) {
                        $rows[] = $r;
                    }
                }
                if (count($rows) < 2) {
                    return;
                }
                $headers = array_map('trim', array_map('strtolower', array_map('strval', array_shift($rows))));
                $required = ['nisn', 'nama_siswa', 'nilai_tugas', 'nilai_uts', 'nilai_uas'];
                if (array_diff($required, $headers)) {
                    Notification::make()->title('Format kolom tidak sesuai')->danger()->send();

                    return;
                }

                $importedCount = 0;

                // Pre-load students in this rombel by NISN to optimize queries
                $studentsInRombel = Student::whereHas('studyGroups', function ($q) use ($studyGroupId) {
                    $q->where('study_groups.id', $studyGroupId);
                })->get()->keyBy('nisn');

                foreach ($rows as $row) {
                    if (count($row) > count($headers)) {
                        $row = array_slice($row, 0, count($headers));
                    } else {
                        $row = array_pad($row, count($headers), '');
                    }
                    $rowData = array_combine($headers, $row);

                    $nisn = trim($rowData['nisn'] ?? '');
                    if (! $nisn || ! isset($studentsInRombel[$nisn])) {
                        continue;
                    }

                    $studentId = $studentsInRombel[$nisn]->id;

                    $tugas = is_numeric($rowData['nilai_tugas']) ? (float) $rowData['nilai_tugas'] : null;
                    $uts = is_numeric($rowData['nilai_uts']) ? (float) $rowData['nilai_uts'] : null;
                    $uas = is_numeric($rowData['nilai_uas']) ? (float) $rowData['nilai_uas'] : null;

                    Grade::updateOrCreate([
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'study_group_id' => $studyGroupId,
                        'academic_year_id' => $activeYearId,
                    ], [
                        'teacher_id' => $teacherId,
                        'nilai_tugas' => $tugas,
                        'nilai_uts' => $uts,
                        'nilai_uas' => $uas,
                    ]);

                    $importedCount++;
                }

                Notification::make()
                    ->title("Sukses Mengimpor Nilai untuk {$importedCount} Siswa")
                    ->success()
                    ->send();
            });
    }

    protected function getBatchInputAction(): Action
    {
        return Action::make('batch_input')
            ->label('Batch Input Nilai')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->modalWidth('7xl')
            ->closeModalByClickingAway(false)
            ->form([
                Section::make('Filter Mata Pelajaran & Rombel')
                    ->schema([
                        Select::make('subject_id')
                            ->label('Pilih Mapel')
                            ->options(function () {
                                $user = auth()->user();
                                $query = Subject::query()->where('is_graded', true);
                                if ($user->hasRole('guru') && $user->teacher) {
                                    $teacherId = $user->teacher->id;
                                    $isWaliKelas = $user->teacher->is_walikelas;

                                    if ($isWaliKelas) {
                                        $query->where(function ($q) use ($teacherId) {
                                            $q->where('is_umum', true)
                                                ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                        });
                                    } else {
                                        $query->where(function ($q) use ($teacherId) {
                                            $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                        });
                                    }
                                }

                                return $query->pluck('nama_mapel', 'id');
                            })
                            ->required()
                            ->live(),
                        Select::make('study_group_id')
                            ->label('Pilih Rombel')
                            ->options(function (Get $get) {
                                $subjectId = $get('subject_id');
                                if (! $subjectId) {
                                    return [];
                                }

                                $user = auth()->user();
                                $activeYearId = AcademicYear::where('is_active', true)->value('id');
                                if (! $activeYearId) {
                                    return [];
                                }

                                $query = StudyGroup::query()
                                    ->where('academic_year_id', $activeYearId);

                                if ($user->hasRole('guru') && $user->teacher) {
                                    $teacherId = $user->teacher->id;
                                    $isWaliKelas = $user->teacher->is_walikelas;

                                    if ($isWaliKelas) {
                                        $subject = Subject::find($subjectId);

                                        if ($subject && $subject->is_umum) {
                                            $query->where('walikelas_id', $teacherId);
                                        } else {
                                            $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                        }
                                    } else {
                                        $hasSchedules = Schedule::where('teacher_id', $teacherId)
                                            ->where('subject_id', $subjectId)
                                            ->exists();

                                        if ($hasSchedules) {
                                            $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                        }
                                    }
                                }

                                return $query->pluck('nama_rombel', 'id');
                            })
                            ->required()
                            ->default(request()->query('study_group_id'))
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::loadStudentsForGrading($get, $set)),
                    ])->columns(2),

                Section::make()
                    ->schema([
                        Html::make(new HtmlString(
                            '<div class="rounded-lg border border-danger-300 bg-danger-50 dark:bg-danger-950/30 dark:border-danger-800 p-4">'.
                            '<div class="flex items-start gap-3">'.
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20" style="flex-shrink:0;color:#dc2626;margin-top:2px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>'.
                            '<div class="text-sm text-danger-700 dark:text-danger-300">'.
                            '<p class="font-semibold">Tujuan Pembelajaran (TP) belum tersedia</p>'.
                            '<p class="mt-1">Belum ada TP aktif untuk mata pelajaran ini di tingkat kelas tersebut. Silakan tambahkan TP terlebih dahulu di menu <strong>Tujuan Pembelajaran</strong> sebelum melakukan input nilai.</p>'.
                            '</div></div></div>'
                        )),
                    ])
                    ->visible(function (Get $get) {
                        $subjectId = $get('subject_id');
                        $studyGroupId = $get('study_group_id');
                        if (! $subjectId || ! $studyGroupId) {
                            return false;
                        }
                        $studyGroup = StudyGroup::find($studyGroupId);
                        if (! $studyGroup) {
                            return false;
                        }

                        return ! LearningObjective::where('subject_id', $subjectId)
                            ->where('level_id', $studyGroup->level_id)
                            ->where('is_active', true)
                            ->exists();
                    }),

                Section::make('Daftar Nilai Siswa')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                Hidden::make('student_id'),
                                Grid::make(12)
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('student_name')
                                                    ->label('Siswa')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('nilai_tugas')
                                                            ->label('Tugas')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100),
                                                        TextInput::make('nilai_uts')
                                                            ->label('UTS')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100),
                                                        TextInput::make('nilai_uas')
                                                            ->label('UAS')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100),
                                                    ]),
                                            ])
                                            ->columnSpan(4),

                                        Grid::make(2)
                                            ->schema([
                                                CheckboxList::make('optimal_tp_ids')
                                                    ->label('TP Tercapai Optimal')
                                                    ->options(function (Get $get) {
                                                        $subjectId = $get('../../subject_id');
                                                        $studyGroupId = $get('../../study_group_id');
                                                        if (! $subjectId || ! $studyGroupId) {
                                                            return [];
                                                        }
                                                        $studyGroup = StudyGroup::find($studyGroupId);
                                                        if (! $studyGroup) {
                                                            return [];
                                                        }

                                                        return LearningObjective::where('subject_id', $subjectId)
                                                            ->where('level_id', $studyGroup->level_id)
                                                            ->where('is_active', true)
                                                            ->get()
                                                            ->pluck('description', 'id')
                                                            ->toArray();
                                                    })
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                        $improved = $get('improved_tp_ids') ?? [];
                                                        $filteredImproved = array_values(array_diff($improved, $state ?? []));
                                                        $set('improved_tp_ids', $filteredImproved);
                                                    })
                                                    ->bulkToggleable(),

                                                CheckboxList::make('improved_tp_ids')
                                                    ->label('TP Perlu Peningkatan')
                                                    ->options(function (Get $get) {
                                                        $subjectId = $get('../../subject_id');
                                                        $studyGroupId = $get('../../study_group_id');
                                                        if (! $subjectId || ! $studyGroupId) {
                                                            return [];
                                                        }
                                                        $studyGroup = StudyGroup::find($studyGroupId);
                                                        if (! $studyGroup) {
                                                            return [];
                                                        }

                                                        return LearningObjective::where('subject_id', $subjectId)
                                                            ->where('level_id', $studyGroup->level_id)
                                                            ->where('is_active', true)
                                                            ->get()
                                                            ->pluck('description', 'id')
                                                            ->toArray();
                                                    })
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                        $optimal = $get('optimal_tp_ids') ?? [];
                                                        $filteredOptimal = array_values(array_diff($optimal, $state ?? []));
                                                        $set('optimal_tp_ids', $filteredOptimal);
                                                    })
                                                    ->bulkToggleable(),
                                            ])
                                            ->columnSpan(8),
                                    ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->visible(function (Get $get) {
                        $subjectId = $get('subject_id');
                        $studyGroupId = $get('study_group_id');
                        if (! $subjectId || ! $studyGroupId) {
                            return false;
                        }
                        $studyGroup = StudyGroup::find($studyGroupId);
                        if (! $studyGroup) {
                            return false;
                        }

                        return LearningObjective::where('subject_id', $subjectId)
                            ->where('level_id', $studyGroup->level_id)
                            ->where('is_active', true)
                            ->exists();
                    }),
            ])
            ->action(function (array $data): void {
                $academicYearId = AcademicYear::where('is_active', true)->first()?->id;
                $teacherId = auth()->user()->teacher?->id;

                $studyGroup = StudyGroup::find($data['study_group_id']);
                $hasTp = $studyGroup && LearningObjective::where('subject_id', $data['subject_id'])
                    ->where('level_id', $studyGroup->level_id)
                    ->where('is_active', true)
                    ->exists();

                if (! $hasTp) {
                    Notification::make()
                        ->title('Gagal menyimpan nilai')
                        ->body('Tujuan Pembelajaran (TP) belum tersedia untuk mata pelajaran ini di tingkat kelas tersebut. Silakan tambahkan TP terlebih dahulu.')
                        ->danger()
                        ->send();

                    return;
                }

                if (empty($data['items'])) {
                    Notification::make()
                        ->title('Tidak ada data nilai untuk disimpan')
                        ->warning()
                        ->send();

                    return;
                }

                foreach ($data['items'] as $item) {
                    Grade::updateOrCreate(
                        [
                            'student_id' => $item['student_id'],
                            'subject_id' => $data['subject_id'],
                            'study_group_id' => $data['study_group_id'],
                            'academic_year_id' => $academicYearId,
                        ],
                        [
                            'teacher_id' => $teacherId,
                            'nilai_tugas' => $item['nilai_tugas'] ?? 0,
                            'nilai_uts' => $item['nilai_uts'] ?? 0,
                            'nilai_uas' => $item['nilai_uas'] ?? 0,
                            'optimal_tp_ids' => $item['optimal_tp_ids'] ?? [],
                            'improved_tp_ids' => $item['improved_tp_ids'] ?? [],
                        ]
                    );
                }

                Notification::make()
                    ->title('Berhasil menyimpan nilai batch')
                    ->success()
                    ->send();
            });
    }

    public static function loadStudentsForGrading($get, $set): void
    {
        $studyGroupId = $get('study_group_id');
        $subjectId = $get('subject_id');

        if (! $studyGroupId || ! $subjectId) {
            $set('items', []);

            return;
        }

        $academicYearId = AcademicYear::where('is_active', true)->value('id');

        $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
            ->with('user')
            ->get();

        $existing = Grade::where('study_group_id', $studyGroupId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('student_id');

        if (request()->query('missing_only')) {
            $students = $students->filter(fn ($student) => ! isset($existing[$student->id]))->values();
        }

        $items = $students->map(fn ($student) => [
            'student_id' => $student->id,
            'student_name' => $student->user?->name ?? 'Unknown',
            'nilai_tugas' => $existing[$student->id]->nilai_tugas ?? null,
            'nilai_uts' => $existing[$student->id]->nilai_uts ?? null,
            'nilai_uas' => $existing[$student->id]->nilai_uas ?? null,
            'optimal_tp_ids' => $existing[$student->id]->optimal_tp_ids ?? [],
            'improved_tp_ids' => $existing[$student->id]->improved_tp_ids ?? [],
        ])->toArray();

        $set('items', $items);
    }
}
