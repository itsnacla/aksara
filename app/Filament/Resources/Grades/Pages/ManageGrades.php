<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGrades extends ManageRecords
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('import_csv')
                ->label('Import Nilai')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->modalHeading('Import Berkas Nilai')
                ->modalDescription(new \Illuminate\Support\HtmlString('Pilih Mata Pelajaran dan Rombel, lalu unggah berkas nilai. Harap pastikan TP (Tujuan Pembelajaran) sudah diatur sebelumnya.<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"><a href="' . route('download.template', ['type' => 'grade', 'format' => 'xlsx']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #10b981; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xlsx)</a><a href="' . route('download.template', ['type' => 'grade', 'format' => 'xls']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #059669; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xls)</a><a href="' . route('download.template', ['type' => 'grade', 'format' => 'csv']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #4b5563; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template CSV</a></div>'))
                ->modalSubmitActionLabel('Mulai Proses Impor')
                ->modalWidth('7xl')
                ->form([
                    \Filament\Schemas\Components\Section::make('Filter Mata Pelajaran & Rombel')
                        ->schema([
                            \Filament\Forms\Components\Select::make('subject_id')
                                ->label('Pilih Mapel')
                                ->options(function () {
                                    $user = auth()->user();
                                    $query = \App\Models\Subject::query()->where('is_graded', true);
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
                            \Filament\Forms\Components\Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                    $subjectId = $get('subject_id');
                                    if (!$subjectId) return [];
                                    $user = auth()->user();
                                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                                    if (!$activeYearId) return [];
                                    $query = \App\Models\StudyGroup::query()->where('academic_year_id', $activeYearId);
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $teacherId = $user->teacher->id;
                                        $isWaliKelas = $user->teacher->is_walikelas;
                                        if ($isWaliKelas) {
                                            $subject = \App\Models\Subject::find($subjectId);
                                            if ($subject && $subject->is_umum) {
                                                $query->where('walikelas_id', $teacherId);
                                            } else {
                                                $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                            }
                                        } else {
                                            $hasSchedules = \App\Models\Schedule::where('teacher_id', $teacherId)->where('subject_id', $subjectId)->exists();
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
                    \Filament\Forms\Components\Hidden::make('parsed_json')->default('[]'),
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Pilih Berkas (CSV / Excel)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) {
                                $set('parsed_json', '[]');
                                return;
                            }
                            $file = is_array($state) ? reset($state) : $state;
                            $path = null;
                            $isTempDownloaded = false;
                            if (is_string($file)) {
                                if (file_exists($file)) {
                                    $path = $file;
                                } elseif (\Illuminate\Support\Facades\Storage::exists($file)) {
                                    $diskDriver = config('filesystems.disks.' . config('filesystems.default') . '.driver');
                                    if (in_array($diskDriver, ['local', 'public'])) {
                                        $path = \Illuminate\Support\Facades\Storage::path($file);
                                    } else {
                                        $tmpPath = storage_path('app/livewire-tmp/' . basename($file));
                                        if (!file_exists(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
                                        file_put_contents($tmpPath, \Illuminate\Support\Facades\Storage::get($file));
                                        $path = $tmpPath;
                                        $isTempDownloaded = true;
                                    }
                                } else {
                                    $tmpPath = storage_path('app/livewire-tmp/' . basename($file));
                                    if (file_exists($tmpPath)) $path = $tmpPath;
                                }
                            } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
                                $path = $file->getRealPath();
                            }
                            if (!$path || !file_exists($path)) return;
                            try {
                                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                                $allRows = $spreadsheet->getActiveSheet()->toArray();
                            } catch (\Exception $e) {
                                if ($isTempDownloaded && file_exists($path)) @unlink($path);
                                return;
                            }
                            if ($isTempDownloaded && file_exists($path)) @unlink($path);
                            $rows = [];
                            foreach ($allRows as $r) {
                                if (array_filter($r, fn($cell) => trim((string)$cell) !== '') !== []) {
                                    $rows[] = $r;
                                }
                            }
                            if (count($rows) < 2) return;
                            $headers = array_map('trim', array_map('strtolower', array_map('strval', array_shift($rows))));
                            $required = ['nisn', 'nama_siswa', 'nilai_tugas', 'nilai_uts', 'nilai_uas'];
                            if (array_diff($required, $headers)) return;

                            $validRows = 0;
                            $previewRows = [];
                            foreach ($rows as $row) {
                                if (count($row) > count($headers)) $row = array_slice($row, 0, count($headers));
                                else $row = array_pad($row, count($headers), '');
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
                    \Filament\Infolists\Components\TextEntry::make('preview')
                        ->label('Pratinjau Tabel Hasil Parsing')
                        ->state(function (callable $get) {
                            $json = $get('parsed_json');
                            $data = json_decode($json, true);
                            if (empty($data) || empty($data['rows'])) {
                                return new \Illuminate\Support\HtmlString('<div class="text-sm text-gray-400 italic p-4 border border-dashed rounded-lg text-center bg-gray-50 dark:bg-white/5">Pilih berkas untuk memuat tabel pratinjau otomatis...</div>');
                            }
                            return view('filament.components.import-preview-table', [
                                'type' => 'grade',
                                'rows' => $data['rows'],
                                'validCount' => $data['valid_count'] ?? 0,
                            ]);
                        }),
                ])
                ->action(function (array $data) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    $teacherId = auth()->user()->teacher?->id;
                    $subjectId = $data['subject_id'];
                    $studyGroupId = $data['study_group_id'];

                    if (!$subjectId || !$studyGroupId) {
                        \Filament\Notifications\Notification::make()->title('Harap pilih mapel dan rombel')->danger()->send();
                        return;
                    }

                    $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                    $path = null;
                    $isTempDownloaded = false;
                    if (is_string($file)) {
                        if (file_exists($file)) {
                            $path = $file;
                        } elseif (\Illuminate\Support\Facades\Storage::exists($file)) {
                            $diskDriver = config('filesystems.disks.' . config('filesystems.default') . '.driver');
                            if (in_array($diskDriver, ['local', 'public'])) {
                                $path = \Illuminate\Support\Facades\Storage::path($file);
                            } else {
                                $tmpPath = storage_path('app/livewire-tmp/' . basename($file));
                                if (!file_exists(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
                                file_put_contents($tmpPath, \Illuminate\Support\Facades\Storage::get($file));
                                $path = $tmpPath;
                                $isTempDownloaded = true;
                            }
                        } else {
                            $tmpPath = storage_path('app/livewire-tmp/' . basename($file));
                            if (file_exists($tmpPath)) $path = $tmpPath;
                        }
                    } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
                        $path = $file->getRealPath();
                    }

                    if (!$path || !file_exists($path)) {
                        \Filament\Notifications\Notification::make()->title('Gagal membaca berkas')->danger()->send();
                        return;
                    }

                    try {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                        $allRows = $spreadsheet->getActiveSheet()->toArray();
                    } catch (\Exception $e) {
                        if ($isTempDownloaded && file_exists($path)) @unlink($path);
                        \Filament\Notifications\Notification::make()->title('Format berkas tidak didukung')->danger()->send();
                        return;
                    }
                    if ($isTempDownloaded && file_exists($path)) @unlink($path);

                    $rows = [];
                    foreach ($allRows as $r) {
                        if (array_filter($r, fn($cell) => trim((string)$cell) !== '') !== []) {
                            $rows[] = $r;
                        }
                    }
                    if (count($rows) < 2) return;
                    $headers = array_map('trim', array_map('strtolower', array_map('strval', array_shift($rows))));
                    $required = ['nisn', 'nama_siswa', 'nilai_tugas', 'nilai_uts', 'nilai_uas'];
                    if (array_diff($required, $headers)) {
                        \Filament\Notifications\Notification::make()->title('Format kolom tidak sesuai')->danger()->send();
                        return;
                    }

                    $importedCount = 0;
                    
                    // Pre-load students in this rombel by NISN to optimize queries
                    $studentsInRombel = \App\Models\Student::whereHas('studyGroups', function ($q) use ($studyGroupId) {
                        $q->where('study_groups.id', $studyGroupId);
                    })->get()->keyBy('nisn');

                    foreach ($rows as $row) {
                        if (count($row) > count($headers)) $row = array_slice($row, 0, count($headers));
                        else $row = array_pad($row, count($headers), '');
                        $rowData = array_combine($headers, $row);

                        $nisn = trim($rowData['nisn'] ?? '');
                        if (!$nisn || !isset($studentsInRombel[$nisn])) continue;

                        $studentId = $studentsInRombel[$nisn]->id;
                        
                        $tugas = is_numeric($rowData['nilai_tugas']) ? (float)$rowData['nilai_tugas'] : null;
                        $uts = is_numeric($rowData['nilai_uts']) ? (float)$rowData['nilai_uts'] : null;
                        $uas = is_numeric($rowData['nilai_uas']) ? (float)$rowData['nilai_uas'] : null;

                        \App\Models\Grade::updateOrCreate([
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

                    \Filament\Notifications\Notification::make()
                        ->title("Sukses Mengimpor Nilai untuk {$importedCount} Siswa")
                        ->success()
                        ->send();
                }),
            \Filament\Actions\Action::make('batch_input')
                ->label('Batch Input Nilai')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false)
                ->form([
                    \Filament\Schemas\Components\Section::make('Filter Mata Pelajaran & Rombel')
                        ->schema([
                            \Filament\Forms\Components\Select::make('subject_id')
                                ->label('Pilih Mapel')
                                ->options(function () {
                                    $user = auth()->user();
                                    $query = \App\Models\Subject::query()->where('is_graded', true);
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $teacherId = $user->teacher->id;
                                        $isWaliKelas = $user->teacher->is_walikelas;
                                        
                                        if ($isWaliKelas) {
                                            // Wali kelas can see: is_umum subjects OR subjects from schedules OR subjects from teacher relationship
                                            $query->where(function ($q) use ($teacherId) {
                                                $q->where('is_umum', true)
                                                  ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                  ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                            });
                                        } else {
                                            // Guru mapel can see: subjects from schedules OR subjects from teacher relationship
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
                            \Filament\Forms\Components\Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                    $subjectId = $get('subject_id');
                                    if (!$subjectId) return [];
                                    
                                    $user = auth()->user();
                                    
                                    // Get active academic year ID (automatic)
                                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                                    if (!$activeYearId) return [];
                                    
                                    // Base query: ALWAYS filter by active academic year
                                    $query = \App\Models\StudyGroup::query()
                                        ->where('academic_year_id', $activeYearId);
                                    
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $teacherId = $user->teacher->id;
                                        $isWaliKelas = $user->teacher->is_walikelas;
                                        
                                        if ($isWaliKelas) {
                                            // Ambil subject untuk cek apakah is_umum
                                            $subject = \App\Models\Subject::find($subjectId);
                                            
                                            if ($subject && $subject->is_umum) {
                                                // Untuk mapel is_umum, wali kelas bisa lihat kelas yang mereka kelola
                                                $query->where('walikelas_id', $teacherId);
                                            } else {
                                                // Untuk mapel non-is_umum, hanya lihat kelas dari jadwal mereka
                                                $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                            }
                                        } else {
                                            // Guru mapel: cek apakah punya schedules untuk subject ini
                                            $hasSchedules = \App\Models\Schedule::where('teacher_id', $teacherId)
                                                ->where('subject_id', $subjectId)
                                                ->exists();
                                            
                                            if ($hasSchedules) {
                                                // Jika punya schedules, hanya lihat kelas dari jadwal mereka
                                                $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                            }
                                            // Jika tidak punya schedules, tampilkan semua rombel di tahun ajaran aktif (sudah di-filter di base query)
                                        }
                                    }
                                    // Untuk super_admin/staff: tampilkan semua rombel di tahun ajaran aktif (sudah di-filter di base query)
                                    
                                    return $query->pluck('nama_rombel', 'id');
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) => self::loadStudentsForGrading($get, $set)),
                        ])->columns(2),
                    
                    \Filament\Schemas\Components\Section::make()
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('tp_warning')
                                ->hiddenLabel()
                                ->content(new \Illuminate\Support\HtmlString(
                                    '<div class="rounded-lg border border-danger-300 bg-danger-50 dark:bg-danger-950/30 dark:border-danger-800 p-4">' .
                                    '<div class="flex items-start gap-3">' .
                                    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20" style="flex-shrink:0;color:#dc2626;margin-top:2px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>' .
                                    '<div class="text-sm text-danger-700 dark:text-danger-300">' .
                                    '<p class="font-semibold">Tujuan Pembelajaran (TP) belum tersedia</p>' .
                                    '<p class="mt-1">Belum ada TP aktif untuk mata pelajaran ini di tingkat kelas tersebut. Silakan tambahkan TP terlebih dahulu di menu <strong>Tujuan Pembelajaran</strong> sebelum melakukan input nilai.</p>' .
                                    '</div></div></div>'
                                )),
                        ])
                        ->visible(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $subjectId = $get('subject_id');
                            $studyGroupId = $get('study_group_id');
                            if (!$subjectId || !$studyGroupId) return false;
                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                            if (!$studyGroup) return false;
                            return !\App\Models\LearningObjective::where('subject_id', $subjectId)
                                ->where('level_id', $studyGroup->level_id)
                                ->where('is_active', true)
                                ->exists();
                        }),

                    \Filament\Schemas\Components\Section::make('Daftar Nilai Siswa')
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('items')
                                ->label('')
                                ->schema([
                                    \Filament\Forms\Components\Hidden::make('student_id'),
                                    \Filament\Schemas\Components\Grid::make(12)
                                        ->schema([
                                            // Left side: Student Name and Grades (4 columns of 12)
                                            \Filament\Schemas\Components\Grid::make(1)
                                                ->schema([
                                                    \Filament\Forms\Components\TextInput::make('student_name')
                                                        ->label('Siswa')
                                                        ->disabled()
                                                        ->dehydrated(false),
                                                    \Filament\Schemas\Components\Grid::make(3)
                                                        ->schema([
                                                            \Filament\Forms\Components\TextInput::make('nilai_tugas')
                                                                ->label('Tugas')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100),
                                                            \Filament\Forms\Components\TextInput::make('nilai_uts')
                                                                ->label('UTS')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100),
                                                            \Filament\Forms\Components\TextInput::make('nilai_uas')
                                                                ->label('UAS')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100),
                                                        ]),
                                                ])
                                                ->columnSpan(4),

                                            // Right side: TP Checklists (8 columns of 12)
                                            \Filament\Schemas\Components\Grid::make(2)
                                                ->schema([
                                                    \Filament\Forms\Components\CheckboxList::make('optimal_tp_ids')
                                                        ->label('TP Tercapai Optimal')
                                                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                            $subjectId = $get('../../subject_id');
                                                            $studyGroupId = $get('../../study_group_id');
                                                            if (!$subjectId || !$studyGroupId) {
                                                                return [];
                                                            }
                                                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                                                            if (!$studyGroup) {
                                                                return [];
                                                            }
                                                            return \App\Models\LearningObjective::where('subject_id', $subjectId)
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

                                                    \Filament\Forms\Components\CheckboxList::make('improved_tp_ids')
                                                        ->label('TP Perlu Peningkatan')
                                                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                            $subjectId = $get('../../subject_id');
                                                            $studyGroupId = $get('../../study_group_id');
                                                            if (!$subjectId || !$studyGroupId) {
                                                                return [];
                                                            }
                                                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                                                            if (!$studyGroup) {
                                                                return [];
                                                            }
                                                            return \App\Models\LearningObjective::where('subject_id', $subjectId)
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
                        ->visible(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $subjectId = $get('subject_id');
                            $studyGroupId = $get('study_group_id');
                            if (!$subjectId || !$studyGroupId) return false;
                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                            if (!$studyGroup) return false;
                            return \App\Models\LearningObjective::where('subject_id', $subjectId)
                                ->where('level_id', $studyGroup->level_id)
                                ->where('is_active', true)
                                ->exists();
                        }),
                ])
                ->action(function (array $data): void {
                    $academicYearId = \App\Models\AcademicYear::where('is_active', true)->first()?->id;
                    $teacherId = auth()->user()->teacher?->id;

                    $studyGroup = \App\Models\StudyGroup::find($data['study_group_id']);
                    $hasTp = $studyGroup && \App\Models\LearningObjective::where('subject_id', $data['subject_id'])
                        ->where('level_id', $studyGroup->level_id)
                        ->where('is_active', true)
                        ->exists();

                    if (!$hasTp) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal menyimpan nilai')
                            ->body('Tujuan Pembelajaran (TP) belum tersedia untuk mata pelajaran ini di tingkat kelas tersebut. Silakan tambahkan TP terlebih dahulu.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if (empty($data['items'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak ada data nilai untuk disimpan')
                            ->warning()
                            ->send();
                        return;
                    }

                    foreach ($data['items'] as $item) {
                        \App\Models\Grade::updateOrCreate(
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

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil menyimpan nilai batch')
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false),
        ];
    }

    public static function loadStudentsForGrading($get, $set): void
    {
        $studyGroupId = $get('study_group_id');
        $subjectId = $get('subject_id');
        
        if (!$studyGroupId || !$subjectId) {
            $set('items', []);
            return;
        }

        $academicYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');

        $students = \App\Models\Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
            ->with('user')
            ->get();

        $existing = \App\Models\Grade::where('study_group_id', $studyGroupId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('student_id');

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
