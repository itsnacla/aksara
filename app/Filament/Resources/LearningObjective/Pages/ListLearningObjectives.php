<?php

namespace App\Filament\Resources\LearningObjective\Pages;

use App\Filament\Resources\LearningObjective\LearningObjectiveResource;
use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\Level;
use App\Models\StudyGroup;
use App\Models\Subject;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ListLearningObjectives extends ListRecords
{
    protected static string $resource = LearningObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_csv')
                ->label('Import Data TP')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Import Berkas Tujuan Pembelajaran')
                ->modalDescription(new HtmlString('Unggah berkas lembar kerja dengan kolom esensial. Sistem akan memparsing otomatis pembuatan Tujuan Pembelajaran sesuai Mata Pelajaran dan Tingkat Kelas yang diketik.<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"><a href="'.route('download.template', ['type' => 'learning-objective', 'format' => 'xlsx']).'" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #10b981; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xlsx)</a><a href="'.route('download.template', ['type' => 'learning-objective', 'format' => 'xls']).'" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #059669; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xls)</a><a href="'.route('download.template', ['type' => 'learning-objective', 'format' => 'csv']).'" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #4b5563; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template CSV</a></div>'))
                ->modalSubmitActionLabel('Mulai Proses Impor')
                ->modalWidth('7xl')
                ->form([
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
                            $required = ['kode_tp', 'deskripsi', 'mata_pelajaran', 'tingkat_kelas'];
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
                                if (trim($data['kode_tp']) && trim($data['deskripsi'])) {
                                    $validRows++;
                                    if (count($previewRows) < 5) {
                                        $previewRows[] = [
                                            'kode_tp' => trim($data['kode_tp']),
                                            'deskripsi' => trim($data['deskripsi']),
                                            'mata_pelajaran' => trim($data['mata_pelajaran']),
                                            'tingkat_kelas' => trim($data['tingkat_kelas']),
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
                                'type' => 'learning-objective',
                                'rows' => $data['rows'],
                                'validCount' => $data['valid_count'] ?? 0,
                            ]);
                        }),
                ])
                ->action(function (array $data) {
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
                    $required = ['kode_tp', 'deskripsi', 'mata_pelajaran', 'tingkat_kelas'];
                    if (array_diff($required, $headers)) {
                        Notification::make()->title('Format kolom tidak sesuai')->danger()->send();

                        return;
                    }

                    $importedCount = 0;
                    $activeYearId = AcademicYear::where('is_active', true)->value('id');

                    // Caches
                    $subjectsCache = [];
                    $levelsCache = [];

                    foreach ($rows as $row) {
                        if (count($row) > count($headers)) {
                            $row = array_slice($row, 0, count($headers));
                        } else {
                            $row = array_pad($row, count($headers), '');
                        }
                        $rowData = array_combine($headers, $row);

                        $kode = trim($rowData['kode_tp'] ?? '');
                        $deskripsi = trim($rowData['deskripsi'] ?? '');
                        $mapelName = trim($rowData['mata_pelajaran'] ?? '');
                        $levelName = trim($rowData['tingkat_kelas'] ?? '');

                        if (! $kode || ! $deskripsi || ! $mapelName || ! $levelName) {
                            continue;
                        }

                        if (! isset($subjectsCache[$mapelName])) {
                            $subj = Subject::where('nama_mapel', 'ilike', '%'.$mapelName.'%')->first();
                            $subjectsCache[$mapelName] = $subj ? $subj->id : null;
                        }
                        if (! isset($levelsCache[$levelName])) {
                            $lvl = Level::where('nama_tingkatan', 'ilike', '%'.$levelName.'%')->first();
                            $levelsCache[$levelName] = $lvl ? $lvl->id : null;
                        }

                        $subjectId = $subjectsCache[$mapelName];
                        $levelId = $levelsCache[$levelName];

                        if ($subjectId && $levelId) {
                            LearningObjective::updateOrCreate([
                                'subject_id' => $subjectId,
                                'level_id' => $levelId,
                                'academic_year_id' => $activeYearId,
                                'code' => $kode,
                            ], [
                                'description' => $deskripsi,
                                'is_active' => true,
                            ]);
                            $importedCount++;
                        }
                    }

                    Notification::make()
                        ->title("Sukses Mengimpor {$importedCount} Data TP")
                        ->success()
                        ->send();
                }),
            Action::make('batch_input_tp')
                ->label('Batch Input TP')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false)
                ->form([
                    Section::make('Filter Mata Pelajaran & Tingkatan')
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
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $user = auth()->user();
                                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                                    if ($isWaliKelas) {
                                        $managedStudyGroup = StudyGroup::where('walikelas_id', $user->teacher->id)
                                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                                            ->first();

                                        $set('level_id', $managedStudyGroup?->level_id);
                                    }
                                }),
                            Select::make('level_id')
                                ->label('Tingkatan / Fase')
                                ->options(function (Get $get) {
                                    $user = auth()->user();
                                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                                    if ($isWaliKelas) {
                                        // For wali kelas, always return their level regardless of subject_id
                                        $managedStudyGroup = StudyGroup::where('walikelas_id', $user->teacher->id)
                                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                                            ->first();

                                        if ($managedStudyGroup && $managedStudyGroup->level) {
                                            return [$managedStudyGroup->level->id => $managedStudyGroup->level->nama_tingkatan];
                                        }

                                        return [];
                                    }

                                    // For guru mapel, require subject_id to be selected first
                                    $subjectId = $get('subject_id');
                                    if (! $subjectId) {
                                        return [];
                                    }

                                    $subject = Subject::find($subjectId);
                                    if ($subject) {
                                        return $subject->levels->pluck('nama_tingkatan', 'id')->toArray();
                                    }

                                    return [];
                                })
                                ->default(function (Get $get) {
                                    $subjectId = $get('subject_id');
                                    if (! $subjectId) {
                                        return null;
                                    }
                                    $user = auth()->user();
                                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;

                                    if ($isWaliKelas) {
                                        $managedStudyGroup = StudyGroup::where('walikelas_id', $user->teacher->id)
                                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                                            ->first();

                                        return $managedStudyGroup?->level_id;
                                    }

                                    return null;
                                })
                                ->disabled(function () {
                                    $user = auth()->user();

                                    return $user && $user->teacher && $user->teacher->is_walikelas;
                                })
                                ->dehydrated()
                                ->live()
                                ->required(),
                        ])->columns(2),

                    Section::make('Daftar TP')
                        ->schema([
                            Repeater::make('items')
                                ->label('')
                                ->minItems(5)
                                ->defaultItems(5)
                                ->schema([
                                    TextInput::make('code')
                                        ->label('Kode TP')
                                        ->placeholder('Contoh: TP 1.1')
                                        ->required(),
                                    Textarea::make('description')
                                        ->label('Deskripsi TP')
                                        ->placeholder('Contoh: Menjelaskan proses fotosintesis...')
                                        ->maxLength(200)
                                        ->required()
                                        ->rows(2),
                                    Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->default(true),
                                ])->columns(1),
                        ]),
                ])
                ->action(function (array $data) {
                    $activeYearId = AcademicYear::where('is_active', true)->value('id');

                    foreach ($data['items'] as $item) {
                        LearningObjective::create([
                            'subject_id' => $data['subject_id'],
                            'level_id' => $data['level_id'],
                            'academic_year_id' => $activeYearId,
                            'code' => $item['code'],
                            'description' => $item['description'],
                            'is_active' => $item['is_active'] ?? true,
                        ]);
                    }

                    Notification::make()
                        ->title('Berhasil')
                        ->body('TP berhasil dibuat!')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()->modalWidth('5xl'),
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.pages.scroll-to-top-script');
    }
}
