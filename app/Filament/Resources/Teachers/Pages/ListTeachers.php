<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Subject;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_csv')
                ->label('Import Data Guru')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Import Berkas Data Guru')
                ->modalDescription(new HtmlString('Unggah berkas lembar kerja dengan kolom esensial. Sistem akan memparsing otomatis pembuatan akun (Username, Email, Password) dan melengkapi isian NIP yang kosong.<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"><a href="' . route('download.template', ['type' => 'teacher', 'format' => 'xlsx']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #10b981; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xlsx)</a><a href="' . route('download.template', ['type' => 'teacher', 'format' => 'xls']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #059669; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xls)</a><a href="' . route('download.template', ['type' => 'teacher', 'format' => 'csv']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #4b5563; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template CSV</a></div>'))
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
                            if (!$state) {
                                $set('parsed_json', '[]');
                                return;
                            }

                            $file = is_array($state) ? reset($state) : $state;
                            $path = null;

                            if (is_string($file)) {
                                if (file_exists($file)) {
                                    $path = $file;
                                } elseif (Storage::disk('public')->exists($file)) {
                                    $path = Storage::disk('public')->path($file);
                                } elseif (Storage::exists($file)) {
                                    $path = Storage::path($file);
                                } else {
                                    $tmpPath = storage_path('app/livewire-tmp/' . $file);
                                    if (file_exists($tmpPath)) {
                                        $path = $tmpPath;
                                    }
                                }
                            } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
                                $path = $file->getRealPath();
                            }

                            if (!$path || !file_exists($path)) {
                                return;
                            }

                            try {
                                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                                $allRows = $spreadsheet->getActiveSheet()->toArray();
                            } catch (\Exception $e) {
                                return;
                            }

                            $rows = [];
                            foreach ($allRows as $r) {
                                if (array_filter($r, fn($cell) => trim((string)$cell) !== '') !== []) {
                                    $rows[] = $r;
                                }
                            }

                            if (count($rows) < 2) {
                                return;
                            }

                            $headers = array_map('trim', array_map('strtolower', array_map('strval', array_shift($rows))));
                            $nameColumn = in_array('nama_lengkap', $headers) ? 'nama_lengkap' : (in_array('user_name', $headers) ? 'user_name' : null);

                            if (!$nameColumn) {
                                return;
                            }

                            $totalRows = count($rows);
                            $validRows = 0;
                            $previewRows = [];
                            
                            // Deterministic synchronization tracking
                            $startNipIndex = Teacher::count() + 1;
                            $simulatedUsernames = [];
                            $simulatedNips = [];
                            $duplicateRows = 0;

                            foreach ($rows as $index => $row) {
                                // Mencegah galat ValueError jika jumlah delimiter baris tidak sama dengan header
                                if (count($row) > count($headers)) {
                                    $row = array_slice($row, 0, count($headers));
                                } else {
                                    $row = array_pad($row, count($headers), '');
                                }
                                $data = array_combine($headers, $row);

                                $name = trim($data[$nameColumn] ?? '');
                                if (!$name) {
                                    continue;
                                }

                                // 1. Deterministic NIP generation matching final execution block
                                $nip = trim($data['nip'] ?? '');
                                if (is_numeric($nip) && str_contains(strtolower($nip), 'e')) {
                                    $nip = number_format((float)$nip, 0, '', '');
                                }
                                $isAutoNip = false;
                                $isDuplicate = false;

                                if (!$nip) {
                                    $nip = 'NIP' . str_pad($startNipIndex, 5, '0', STR_PAD_LEFT);
                                    while (Teacher::where('nip', $nip)->exists() || in_array($nip, $simulatedNips)) {
                                        $startNipIndex++;
                                        $nip = 'NIP' . str_pad($startNipIndex, 5, '0', STR_PAD_LEFT);
                                    }
                                    $startNipIndex++;
                                    $isAutoNip = true;
                                    $simulatedNips[] = $nip;
                                } else {
                                    if (Teacher::where('nip', $nip)->exists() || in_array($nip, $simulatedNips)) {
                                        $isDuplicate = true;
                                        $duplicateRows++;
                                    } else {
                                        $simulatedNips[] = $nip;
                                    }
                                }

                                $status = trim($data['status_guru'] ?? '') ?: 'aktif';
                                $mapelRaw = trim($data['mata_pelajaran'] ?? '');
                                $mapelArray = array_filter(array_map('trim', preg_split('/[,;|]/', $mapelRaw)));
                                $mapelDisplay = empty($mapelArray) ? '-' : implode(', ', $mapelArray);

                                $validRows++;
                                // Menyimpan data baris untuk pratinjau dengan membuang gelar akademik
                                $cleanName = explode(',', $name)[0];
                                $cleanName = preg_replace('/\b(dr|drs|drg|prof|hj|h|ir)\b\.?/i', '', $cleanName);
                                $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                                if (!$baseUsername) {
                                    $baseUsername = 'guru' . rand(100, 999);
                                }

                                $username = $baseUsername;
                                $suffix = 1;
                                while (User::where('username', $username)->exists() || in_array($username, $simulatedUsernames)) {
                                    $username = $baseUsername . $suffix;
                                    $suffix++;
                                }
                                $simulatedUsernames[] = $username;

                                $email = $username . '@aksara.com';

                                $previewRows[] = [
                                    'name' => $name,
                                    'gelar_depan' => trim($data['gelar_depan'] ?? ''),
                                    'gelar_belakang' => trim($data['gelar_belakang'] ?? ''),
                                    'nip' => $nip,
                                    'is_auto_nip' => $isAutoNip,
                                    'is_duplicate' => $isDuplicate,
                                    'status' => ucfirst($status),
                                    'username' => $username,
                                    'email' => $email,
                                    'mapel' => $mapelDisplay,
                                ];
                            }

                            $payload = [
                                'valid_count' => $validRows,
                                'duplicate_count' => $duplicateRows,
                                'rows' => $previewRows,
                            ];

                            $set('parsed_json', json_encode($payload));
                        }),
                    \Filament\Infolists\Components\TextEntry::make('preview')
                        ->label('Pratinjau Tabel Hasil Parsing')
                        ->state(function (callable $get) {
                            $json = $get('parsed_json');
                            $data = json_decode($json, true);
                            if (empty($data) || empty($data['rows'])) {
                                return new HtmlString('<div class="text-sm text-gray-400 italic p-4 border border-dashed rounded-lg text-center bg-gray-50 dark:bg-white/5">Pilih berkas untuk memuat tabel pratinjau otomatis...</div>');
                            }
                            return view('filament.components.import-preview-table', [
                                'type' => 'teacher',
                                'rows' => $data['rows'],
                                'validCount' => $data['valid_count'] ?? 0,
                                'duplicateCount' => $data['duplicate_count'] ?? 0,
                            ]);
                        }),
                ])
                ->action(function (array $data, Action $action) {
                    $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                    $path = null;

                    if (is_string($file)) {
                        if (file_exists($file)) {
                            $path = $file;
                        } elseif (Storage::disk('public')->exists($file)) {
                            $path = Storage::disk('public')->path($file);
                        } elseif (Storage::exists($file)) {
                            $path = Storage::path($file);
                        } else {
                            $tmpPath = storage_path('app/livewire-tmp/' . $file);
                            if (file_exists($tmpPath)) {
                                $path = $tmpPath;
                            }
                        }
                    } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
                        $path = $file->getRealPath();
                    }

                    if (!$path || !file_exists($path)) {
                        Notification::make()
                            ->title('Gagal membaca berkas')
                            ->body('Berkas tidak ditemukan pada penyimpanan sementara.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                        $allRows = $spreadsheet->getActiveSheet()->toArray();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Format berkas tidak didukung')
                            ->body('Pastikan berkas berformat CSV atau Excel (.xlsx/.xls) yang valid.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $rows = [];
                    foreach ($allRows as $r) {
                        if (array_filter($r, fn($cell) => trim((string)$cell) !== '') !== []) {
                            $rows[] = $r;
                        }
                    }

                    if (count($rows) < 2) {
                        Notification::make()
                            ->title('Berkas kosong')
                            ->body('Tidak ada baris data yang dapat diimpor.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $headers = array_map('trim', array_map('strtolower', array_map('strval', array_shift($rows))));
                    $nameColumn = in_array('nama_lengkap', $headers) ? 'nama_lengkap' : (in_array('user_name', $headers) ? 'user_name' : null);

                    if (!$nameColumn) {
                        Notification::make()
                            ->title('Format berkas tidak sesuai')
                            ->body('Kolom nama_lengkap wajib ada pada baris pertama berkas impor.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $importedCount = 0;
                    $skippedCount = 0;
                    $startNipIndex = Teacher::count() + 1;
                    $insertedUsernames = [];
                    $insertedNips = [];

                    foreach ($rows as $index => $row) {
                        // Normalisasi array slice/pad agar array_combine aman
                        if (count($row) > count($headers)) {
                            $row = array_slice($row, 0, count($headers));
                        } else {
                            $row = array_pad($row, count($headers), '');
                        }
                        $rowData = array_combine($headers, $row);

                        $name = trim($rowData[$nameColumn] ?? '');
                        if (!$name) {
                            continue;
                        }

                        // Handling NIP Kosong -> NIP0000n deterministically
                        $nip = trim($rowData['nip'] ?? '');
                        if (is_numeric($nip) && str_contains(strtolower($nip), 'e')) {
                            $nip = number_format((float)$nip, 0, '', '');
                        }
                        if (!$nip) {
                            $nip = 'NIP' . str_pad($startNipIndex, 5, '0', STR_PAD_LEFT);
                            while (Teacher::where('nip', $nip)->exists() || in_array($nip, $insertedNips)) {
                                $startNipIndex++;
                                $nip = 'NIP' . str_pad($startNipIndex, 5, '0', STR_PAD_LEFT);
                            }
                            $startNipIndex++;
                            $insertedNips[] = $nip;
                        } else {
                            // Jika NIP sudah ada di database atau dideteksi duplikat di dalam file,
                            // maka baris ini sepenuhnya dilewati (diabaikan) sesuai arahan sistem.
                            if (Teacher::where('nip', $nip)->exists() || in_array($nip, $insertedNips)) {
                                $skippedCount++;
                                continue;
                            }
                            $insertedNips[] = $nip;
                        }

                        // Membersihkan gelar akademik secara cerdas
                        $cleanName = explode(',', $name)[0];
                        $cleanName = preg_replace('/\b(dr|drs|drg|prof|hj|h|ir)\b\.?/i', '', $cleanName);
                        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                        if (!$baseUsername) {
                            $baseUsername = 'guru' . rand(100, 999);
                        }

                        $username = $baseUsername;
                        $suffix = 1;
                        while (User::where('username', $username)->exists() || in_array($username, $insertedUsernames)) {
                            $username = $baseUsername . $suffix;
                            $suffix++;
                        }
                        $insertedUsernames[] = $username;

                        $email = $username . '@aksara.com';
                        $password = 'password';

                        // 1. Create User
                        $user = User::create([
                            'name' => $name,
                            'username' => $username,
                            'email' => $email,
                            'password' => Hash::make($password),
                        ]);
                        $user->syncRoles(['guru']);

                        // 2. Create Teacher
                        $status = trim($rowData['status_guru'] ?? '') ?: 'aktif';
                        $teacher = Teacher::create([
                            'user_id' => $user->id,
                            'gelar_depan' => trim($rowData['gelar_depan'] ?? null) ?: null,
                            'gelar_belakang' => trim($rowData['gelar_belakang'] ?? null) ?: null,
                            'nip' => $nip,
                            'status' => strtolower($status),
                            'no_whatsapp' => trim($rowData['no_whatsapp'] ?? '') ?: null,
                            'is_walikelas' => filter_var($rowData['wali_kelas'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'is_kepalasekolah' => filter_var($rowData['kepala_sekolah'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        ]);

                        // 3. Attach Subjects (Multi / Array support)
                        if (!empty($rowData['mata_pelajaran'])) {
                            // Split multi subject values gracefully by comma, semicolon, or line pipes
                            $mapelNames = preg_split('/[,;|]/', $rowData['mata_pelajaran']);
                            $subjectIds = [];
                            foreach ($mapelNames as $mName) {
                                $mName = trim($mName);
                                if ($mName) {
                                    $subj = Subject::where('nama_mapel', 'ilike', '%' . $mName . '%')->first();
                                    if ($subj) {
                                        $subjectIds[] = $subj->id;
                                    }
                                }
                            }
                            if (!empty($subjectIds)) {
                                $teacher->subjects()->syncWithoutDetaching($subjectIds);
                            }
                        }

                        $importedCount++;
                    }

                    Notification::make()
                        ->title("Proses Impor Selesai: {$importedCount} Guru Ditambahkan")
                        ->body($skippedCount > 0 
                            ? "Sebanyak <b>{$skippedCount} baris data dilewati</b> karena NIP sudah terdaftar." 
                            : "Seluruh data berhasil diparsing dengan sandi default 'password'")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Create User account
                    $user = User::create([
                        'name' => $data['user_name'],
                        'username' => $data['user_username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'is_active' => $data['user_is_active'] ?? true,
                    ]);

                    // Assign role
                    $user->syncRoles(['guru']);

                    // Set user_id on teacher data
                    $data['user_id'] = $user->id;

                    // Remove user fields from data
                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_is_active']);

                    return $data;
                }),
        ];
    }
}
