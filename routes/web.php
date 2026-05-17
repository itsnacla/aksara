<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\ChatbotController;

// Root redirect to Admin (Makes the app start with login)
Route::get('/', function () {
    return redirect('/admin');
});

// Alias login for middleware compatibility
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Portal (Students/Parents)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PortalController::class, 'index'])->name('dashboard');

    // Student Leaves (Permissions)
    Route::get('/leaves', [\App\Http\Controllers\Portal\StudentLeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [\App\Http\Controllers\Portal\StudentLeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [\App\Http\Controllers\Portal\StudentLeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}/edit', [\App\Http\Controllers\Portal\StudentLeaveController::class, 'edit'])->name('leaves.edit');
    Route::put('/leaves/{leave}', [\App\Http\Controllers\Portal\StudentLeaveController::class, 'update'])->name('leaves.update');

    Route::post('/logout', [PortalController::class, 'logout'])->name('logout');

    // AI Chatbot
    Route::get('/chatbot/config', [ChatbotController::class, 'config'])->name('chatbot.config');
    Route::post('/chatbot/chat', [ChatbotController::class, 'chat'])->name('chatbot.chat');

    // Student Cards
    Route::get('/student-card/{student}', [\App\Http\Controllers\StudentCardController::class, 'print'])->name('student.card');
    Route::get('/student-cards/bulk', [\App\Http\Controllers\StudentCardController::class, 'bulkPrint'])->name('student.cards.bulk');
    Route::get('/student-cards/all', [\App\Http\Controllers\StudentCardController::class, 'allPrint'])->name('student.cards.all');
    Route::get('/student-cards/rombel/{studyGroupId}', [\App\Http\Controllers\StudentCardController::class, 'printByStudyGroup'])->name('student.cards.rombel');
    
    // Reports
    Route::get('/reports/attendance', [\App\Http\Controllers\ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/schedule', [\App\Http\Controllers\ReportController::class, 'schedule'])->name('reports.schedule');

    // Standalone QR Scan
    Route::get('/scan-presensi', \App\Livewire\QrScanStandalone::class)
        ->name('scan-presensi')
        ->middleware(['auth', 'can:scan_attendance']);

    // Download Template Multi-Format untuk Impor Massal (CSV, XLSX, XLS)
    Route::get('/download-template/{type}/{format?}', function (string $type, string $format = 'csv') {
        $columns = [];
        $examples = [];

        if ($type === 'teacher') {
            $columns = ['gelar_depan', 'nama_lengkap', 'gelar_belakang', 'nip', 'status_guru', 'no_whatsapp', 'wali_kelas', 'kepala_sekolah', 'mata_pelajaran'];
            $examples = [
                ['Drs.', 'Budi Santoso', 'S.Pd.', '198001012005011001', 'aktif', '081234567890', '1', '0', 'Matematika'],
                ['Dr. Hj.', 'Siti Aminah', 'M.Si.', '', 'aktif', '08555666777', '0', '1', 'Fisika, Kimia'],
                ['', 'Ahmad Rivan', '', '199203032018021002', 'mutasi', '08111222333', '0', '0', 'Bahasa Inggris|Seni Budaya']
            ];
        } elseif ($type === 'student') {
            $columns = [
                'user_name', 'nisn', 'nis', 'gender', 'pob', 'dob', 'religion', 'phone', 'address', 'previous_school',
                'nama_rombel',
                'parent_father_name', 'parent_mother_name', 'parent_whatsapp', 'parent_address', 
                'parent_province', 'parent_city', 'parent_district', 'parent_village',
                'guardian_name', 'guardian_occupation', 'guardian_address'
            ];
            $examples = [
                [
                    'Ahmad Fauzi', '0051234567', '12345', 'L', 'Jakarta', '2010-05-12', 'Islam', '08111222333', 'Jl. Merdeka No 10', 'SMPN 1 Jakarta',
                    'Kelas 1 - Ruang 1', 
                    'Hendra Fauzi', 'Siti Fauzi', '081299998888', 'Jl. Merdeka No 10',
                    'JAWA BARAT', 'KOTA BANDUNG', 'COBLONG', 'SILIWANGI',
                    '', '', ''
                ]
            ];
        } elseif ($type === 'staff') {
            $columns = ['nama_lengkap', 'jabatan', 'status_pegawai', 'no_whatsapp'];
            $examples = [
                ['Siti Aminah', 'Administrasi Keuangan', 'aktif', '08555666777'],
                ['Hendra Gunawan', 'Kepala Tata Usaha', 'aktif', '081233334444']
            ];
        } else {
            abort(404, 'Template tidak ditemukan.');
        }

        if ($format === 'xlsx') {
            $filename = "template_import_{$type}.xlsx";
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->streamDownload(function () use ($columns, $examples) {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $data = [$columns];
                foreach ($examples as $example) {
                    $data[] = $example;
                }
                foreach ($data as $rowIndex => $row) {
                    foreach ($row as $colIndex => $value) {
                        $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
                        $sheet->setCellValueExplicit($cellCoordinate, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                foreach (range(1, count($columns)) as $col) {
                    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, $headers);
        } elseif ($format === 'xls') {
            $filename = "template_import_{$type}.xls";
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->streamDownload(function () use ($columns, $examples) {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $data = [$columns];
                foreach ($examples as $example) {
                    $data[] = $example;
                }
                foreach ($data as $rowIndex => $row) {
                    foreach ($row as $colIndex => $value) {
                        $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
                        $sheet->setCellValueExplicit($cellCoordinate, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                foreach (range(1, count($columns)) as $col) {
                    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                $writer->save('php://output');
            }, $filename, $headers);
        }

        // Default Fallback CSV
        $filename = "template_import_{$type}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($columns, $examples) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($examples as $example) {
                fputcsv($file, $example);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    })->name('download.template');

    // Cetak Buku Induk & Rapor
    Route::get('/buku-induk/print/{student}', [\App\Http\Controllers\PrintController::class, 'printBukuInduk'])->name('print.buku-induk');
    Route::get('/rapor/print-bulk', [\App\Http\Controllers\PrintController::class, 'printRaporBulk'])->name('print.rapor.bulk');
    Route::get('/rapor/print/{student}', [\App\Http\Controllers\PrintController::class, 'printRapor'])->name('print.rapor');
});

