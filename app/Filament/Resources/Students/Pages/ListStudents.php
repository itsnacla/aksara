<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudyGroup;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_all')
                ->label('Cetak Seluruh Kartu')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->form([
                    Select::make('academic_year_id')
                        ->label('Pilih Tahun Ajaran')
                        ->options(fn () => \App\Models\AcademicYear::query()
                            ->get()
                            ->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - " . ucfirst($year->semester)])
                        )
                        ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id)
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('student.cards.all', ['academic_year_id' => $data['academic_year_id']]);
                }),
            Action::make('import_csv')
                ->label('Import Data Siswa')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Import Berkas Data Siswa & Wali')
                ->modalDescription(new HtmlString('Unggah berkas lembar kerja data induk siswa. Sistem akan memindai kolom, memvalidasi format, serta menghasilkan akun Siswa dan akun Orang Tua secara otomatis bila dikosongkan.<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"><a href="' . route('download.template', ['type' => 'student', 'format' => 'xlsx']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #10b981; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xlsx)</a><a href="' . route('download.template', ['type' => 'student', 'format' => 'xls']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #059669; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xls)</a><a href="' . route('download.template', ['type' => 'student', 'format' => 'csv']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #4b5563; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template CSV</a></div>'))
                ->modalSubmitActionLabel('Mulai Proses Impor')
                ->modalWidth('7xl')
                ->form([
                    Select::make('fallback_study_group_id')
                        ->label('Rombel / Kelas Tujuan (Opsional)')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))->get()->mapWithKeys(fn ($sg) => [
                            $sg->id => "{$sg->nama_rombel} ({$sg->academicYear->tahun_ajaran})"
                        ]))
                        ->searchable()
                        ->helperText('Jika kolom nama_rombel di CSV kosong, siswa akan otomatis dimasukkan ke Rombel ini.'),
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
                            $required = ['user_name', 'nisn', 'gender'];
                            $missing = array_diff($required, $headers);

                            if (!empty($missing)) {
                                return;
                            }

                            $totalRows = count($rows);
                            $validRows = 0;
                            $duplicateRows = 0;
                            $previewRows = [];
                            $simulatedUsernames = [];
                            $simulatedNisns = [];

                            foreach ($rows as $index => $row) {
                                if (count($row) > count($headers)) {
                                    $row = array_slice($row, 0, count($headers));
                                } else {
                                    $row = array_pad($row, count($headers), '');
                                }
                                $data = array_combine($headers, $row);

                                $name = trim($data['user_name'] ?? '');
                                $nisn = trim($data['nisn'] ?? '');
                                if (is_numeric($nisn) && str_contains(strtolower($nisn), 'e')) {
                                    $nisn = number_format((float)$nisn, 0, '', '');
                                }

                                if ($name && $nisn) {
                                    $isDuplicate = false;
                                    if (Student::where('nisn', $nisn)->exists() || in_array($nisn, $simulatedNisns)) {
                                        $isDuplicate = true;
                                        $duplicateRows++;
                                    } else {
                                        $simulatedNisns[] = $nisn;
                                    }

                                    $validRows++;
                                    $username = trim($data['user_username'] ?? '');
                                    if (!$username) {
                                        $cleanName = explode(',', $name)[0];
                                        $cleanName = preg_replace('/\b(dr|drs|drg|prof|hj|h|ir)\b\.?/i', '', $cleanName);
                                        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                                        if (!$baseUsername) {
                                            $baseUsername = 'siswa' . rand(100, 999);
                                        }
                                        $username = $baseUsername;
                                        $suffix = 1;
                                        while (User::where('username', $username)->exists() || in_array($username, $simulatedUsernames)) {
                                            $username = $baseUsername . $suffix;
                                            $suffix++;
                                        }
                                    }
                                    $simulatedUsernames[] = $username;

                                    $parentName = trim($rowData['parent_user_name'] ?? '') ?: (trim($rowData['parent_name'] ?? '') ?: "Wali {$name}");
                                    $rombel = trim($rowData['nama_rombel'] ?? '') ?: 'Ikut Form / Fallback';

                                    $previewRows[] = [
                                        'name' => $name,
                                        'nisn' => $nisn,
                                        'username' => $username,
                                        'parent' => $parentName,
                                        'rombel' => $rombel,
                                        'is_duplicate' => $isDuplicate,
                                    ];
                                }
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
                                'type' => 'student',
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

                    $importedCount = 0;
                    $skippedCount = 0;
                    $insertedUsernames = [];
                    $insertedNisns = [];

                    foreach ($rows as $row) {
                        if (count($row) > count($headers)) {
                            $row = array_slice($row, 0, count($headers));
                        } else {
                            $row = array_pad($row, count($headers), '');
                        }
                        $rowData = array_combine($headers, $row);

                        $name = trim($rowData['user_name'] ?? '');
                        $nisn = trim($rowData['nisn'] ?? '');
                        if (is_numeric($nisn) && str_contains(strtolower($nisn), 'e')) {
                            $nisn = number_format((float)$nisn, 0, '', '');
                        }

                        if (!$name || !$nisn) {
                            continue;
                        }

                        // Check duplicate NISN
                        if (Student::where('nisn', $nisn)->exists() || in_array($nisn, $insertedNisns)) {
                            $skippedCount++;
                            continue;
                        }
                        $insertedNisns[] = $nisn;

                        // 1. Generate Student Credentials
                        $cleanName = explode(',', $name)[0];
                        $cleanName = preg_replace('/\b(dr|drs|drg|prof|hj|h|ir)\b\.?/i', '', $cleanName);
                        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                        if (!$baseUsername) { $baseUsername = 'siswa' . rand(100, 999); }
                        
                        $username = $baseUsername;
                        $suffix = 1;
                        while (User::where('username', $username)->exists() || in_array($username, $insertedUsernames)) {
                            $username = $baseUsername . $suffix;
                            $suffix++;
                        }
                        $insertedUsernames[] = $username;
                        $email = $username . '@aksara.samastanuswantara.com';

                        $studentUser = User::create([
                            'name' => $name,
                            'username' => $username,
                            'email' => $email,
                            'password' => Hash::make('password'),
                        ]);
                        $studentUser->syncRoles(['siswa']);

                        // 2. Generate Parent Credentials
                        $parentName = trim($rowData['parent_father_name'] ?? '') ?: (trim($rowData['parent_mother_name'] ?? '') ?: "Wali {$name}");
                        $parentUsername = 'wali_' . $username;
                        $parentEmail = 'wali_' . $email;

                        // Ensure parent credentials are also unique
                        if (User::where('username', $parentUsername)->orWhere('email', $parentEmail)->exists()) {
                            $parentUsername .= rand(10, 99);
                            $parentEmail = $parentUsername . '@aksara.samastanuswantara.com';
                        }

                        $parentUser = User::create([
                            'name' => $parentName,
                            'username' => $parentUsername,
                            'email' => $parentEmail,
                            'password' => Hash::make('password'),
                        ]);
                        $parentUser->syncRoles(['wali']);

                        $studentParent = StudentParent::create([
                            'user_id' => $parentUser->id,
                            'no_whatsapp' => trim($rowData['parent_whatsapp'] ?? '') ?: null,
                            'hubungan' => 'wali',
                            'father_name' => trim($rowData['parent_father_name'] ?? '') ?: null,
                            'mother_name' => trim($rowData['parent_mother_name'] ?? '') ?: null,
                            'address' => trim($rowData['parent_address'] ?? '') ?: null,
                            'province' => trim($rowData['parent_province'] ?? '') ?: null,
                            'city' => trim($rowData['parent_city'] ?? '') ?: null,
                            'district' => trim($rowData['parent_district'] ?? '') ?: null,
                            'village' => trim($rowData['parent_village'] ?? '') ?: null,
                            'guardian_name' => trim($rowData['guardian_name'] ?? '') ?: null,
                            'guardian_occupation' => trim($rowData['guardian_occupation'] ?? '') ?: null,
                            'guardian_address' => trim($rowData['guardian_address'] ?? '') ?: null,
                        ]);

                        // 3. Create Student
                        $student = Student::create([
                            'user_id' => $studentUser->id,
                            'parent_id' => $studentParent->id,
                            'nisn' => $nisn,
                            'nis' => trim($rowData['nis'] ?? '') ?: null,
                            'status' => trim($rowData['status'] ?? '') ?: 'aktif',
                            'gender' => strtoupper(trim($rowData['gender'] ?? '')) === 'P' ? 'P' : 'L',
                            'pob' => trim($rowData['pob'] ?? '') ?: null,
                            'dob' => trim($rowData['dob'] ?? '') ?: null,
                            'religion' => trim($rowData['religion'] ?? '') ?: null,
                            'phone' => trim($rowData['phone'] ?? '') ?: null,
                            'address' => trim($rowData['address'] ?? '') ?: null,
                            'previous_school' => trim($rowData['previous_school'] ?? '') ?: null,
                        ]);

                        // 4. Map Study Group (Rombel)
                        $rombelId = null;
                        if (!empty($rowData['nama_rombel'])) {
                            $sg = StudyGroup::where('nama_rombel', 'ilike', '%' . trim($rowData['nama_rombel']) . '%')->first();
                            if ($sg) {
                                $rombelId = $sg->id;
                            }
                        }
                        if (!$rombelId && !empty($data['fallback_study_group_id'])) {
                            $rombelId = $data['fallback_study_group_id'];
                        }

                        if ($rombelId) {
                            $student->studyGroups()->syncWithoutDetaching([$rombelId]);
                        }

                        $importedCount++;
                    }

                    Notification::make()
                        ->title("Proses Impor Selesai: {$importedCount} Siswa Ditambahkan")
                        ->body($skippedCount > 0 
                            ? "Sebanyak <b>{$skippedCount} baris data dilewati</b> karena NISN sudah terdaftar." 
                            : "Akun siswa dan wali berhasil diparsing otomatis dengan sandi default 'password'")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->modalWidth('7xl')
                ->mutateFormDataUsing(function (array $data): array {
                    // 1. Handle Parent Creation if requested
                    if (isset($data['create_new_parent']) && $data['create_new_parent']) {
                        $parentUser = User::create([
                            'name' => $data['parent_name'],
                            'username' => $data['parent_username'],
                            'email' => $data['parent_email'],
                            'password' => Hash::make($data['parent_password']),
                        ]);

                        $parentUser->syncRoles(['wali']);

                        $studentParent = StudentParent::create([
                            'user_id' => $parentUser->id,
                            'no_whatsapp' => $data['parent_whatsapp'] ?? null,
                            'hubungan' => 'wali',
                            'father_name' => $data['parent']['father_name'] ?? null,
                            'father_occupation' => $data['parent']['father_occupation'] ?? null,
                            'mother_name' => $data['parent']['mother_name'] ?? null,
                            'mother_occupation' => $data['parent']['mother_occupation'] ?? null,
                            'address' => $data['parent']['address'] ?? null,
                            'village' => $data['parent']['village'] ?? null,
                            'district' => $data['parent']['district'] ?? null,
                            'city' => $data['parent']['city'] ?? null,
                            'province' => $data['parent']['province'] ?? null,
                            'guardian_name' => $data['parent']['guardian_name'] ?? null,
                            'guardian_occupation' => $data['parent']['guardian_occupation'] ?? null,
                            'guardian_address' => $data['parent']['guardian_address'] ?? null,
                        ]);

                        $data['parent_id'] = $studentParent->id;
                    }

                    // Clean up parent fields from student data
                    unset(
                        $data['create_new_parent'],
                        $data['parent_name'],
                        $data['parent_username'],
                        $data['parent_email'],
                        $data['parent_password'],
                        $data['parent_whatsapp']
                    );

                    // 2. Handle Student User Creation
                    $user = User::create([
                        'name' => $data['user_name'],
                        'username' => $data['user_username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                    ]);

                    // Assign role
                    $user->syncRoles(['siswa']);

                    // Set user_id on student data
                    $data['user_id'] = $user->id;

                    // Remove user and nested parent fields from data, keep only student fields
                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['parent']);

                    return $data;
                }),
        ];
    }
}
