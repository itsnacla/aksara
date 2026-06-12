<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use App\Models\Staff;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_csv')
                ->label('Import Data Staff')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Import Berkas Data Staff')
                ->modalDescription(new HtmlString('Unggah berkas lembar kerja data induk staf/pegawai. Sistem akan memvalidasi kolom dan langsung menghasilkan akun pengguna secara otomatis bila dikosongkan.<div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;"><a href="' . route('download.template', ['type' => 'staff', 'format' => 'xlsx']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #10b981; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xlsx)</a><a href="' . route('download.template', ['type' => 'staff', 'format' => 'xls']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #059669; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template Excel (.xls)</a><a href="' . route('download.template', ['type' => 'staff', 'format' => 'csv']) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #4b5563; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);"><svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Unduh Template CSV</a></div>'))
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
                            $simulatedUsernames = [];

                            foreach ($rows as $index => $row) {
                                if (count($row) > count($headers)) {
                                    $row = array_slice($row, 0, count($headers));
                                } else {
                                    $row = array_pad($row, count($headers), '');
                                }
                                $data = array_combine($headers, $row);

                                $name = trim($data[$nameColumn] ?? '');
                                $jabatan = trim($data['jabatan'] ?? '');

                                if ($name && $jabatan) {
                                    $validRows++;
                                    if (count($previewRows) < 5) {
                                        $cleanName = explode(',', $name)[0];
                                        $cleanName = preg_replace('/\b(dr|drs|drg|prof|hj|h|ir)\b\.?/i', '', $cleanName);
                                        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                                        if (!$baseUsername) {
                                            $baseUsername = 'staff' . rand(100, 999);
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
                                            'jabatan' => $jabatan,
                                            'username' => $username,
                                            'email' => $email,
                                        ];
                                    }
                                }
                            }

                            $payload = [
                                'valid_count' => $validRows,
                                'duplicate_count' => 0,
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
                                'type' => 'staff',
                                'rows' => $data['rows'],
                                'validCount' => $data['valid_count'] ?? 0,
                                'duplicateCount' => 0,
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
                    $insertedUsernames = [];

                    foreach ($rows as $row) {
                        if (count($row) > count($headers)) {
                            $row = array_slice($row, 0, count($headers));
                        } else {
                            $row = array_pad($row, count($headers), '');
                        }
                        $rowData = array_combine($headers, $row);

                        $name = trim($rowData[$nameColumn] ?? '');
                        $jabatan = trim($rowData['jabatan'] ?? '');

                        if (!$name || !$jabatan) {
                            continue;
                        }

                        // Auto parsing fallback secara cerdas
                        $cleanName = explode(',', $name)[0];
                        $cleanName = preg_replace('/\b(dr|drs|drg|prof|hj|h|ir)\b\.?/i', '', $cleanName);
                        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                        if (!$baseUsername) {
                            $baseUsername = 'staff' . rand(100, 999);
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

                        // Check unique email/username
                        if (User::where('email', $email)->orWhere('username', $username)->exists()) {
                            $username .= rand(100, 999);
                            $email = $username . '@aksara.com';
                        }

                        // 1. Create User
                        $user = User::create([
                            'name' => $name,
                            'username' => $username,
                            'email' => $email,
                            'password' => Hash::make($password),
                        ]);
                        $user->syncRoles(['staff']);

                        // 2. Create Staff
                        Staff::create([
                            'user_id' => $user->id,
                            'jabatan' => $jabatan,
                            'status' => trim($rowData['status_pegawai'] ?? '') ?: (trim($rowData['status'] ?? '') ?: 'aktif'),
                            'no_whatsapp' => trim($rowData['no_whatsapp'] ?? '') ?: null,
                        ]);

                        $importedCount++;
                    }

                    Notification::make()
                        ->title("Sukses Mengimpor {$importedCount} Data Staff")
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
                    $user->syncRoles(['staff']);

                    // Set user_id on staff data
                    $data['user_id'] = $user->id;

                    // Remove user fields from data
                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_is_active']);

                    return $data;
                }),
        ];
    }
}
