<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Peserta Didik - {{ isset($isBulk) && $isBulk ? 'Cetak Masal' : $student->user->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                background-color: white !important;
            }
            .no-print { display: none !important; }
            .print-container { 
                box-shadow: none !important; 
                margin: 0 auto !important; 
                width: 100% !important;
                max-width: 100% !important;
                height: 100vh !important;
                min-height: 100vh !important;
                display: flex !important;
                flex-direction: column !important;
                box-sizing: border-box !important;
                page-break-inside: avoid !important;
            }
            .page-break {
                display: block !important;
                visibility: visible !important;
                page-break-before: always !important;
                break-before: page !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                height: 0 !important;
                min-height: 0 !important;
            }
            .page-break::after {
                display: none !important;
            }
        }
        body { font-family: Arial, Helvetica, sans-serif; }
        .print-container {
            display: flex;
            flex-direction: column;
            min-height: 297mm; /* Standard A4 height on screen */
            box-sizing: border-box;
        }
        .print-footer {
            margin-top: auto !important;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .table-custom th {
            background-color: #eff6ff !important; /* Biru muda banget (Tailwind bg-blue-50) */
            color: #000000 !important;
            font-weight: bold;
        }
        .group-header {
            background-color: transparent !important;
        }
        .page-break {
            border-top: 2px dashed #9ca3af;
            margin: 40px 0;
            position: relative;
        }
        .page-break::after {
            content: "HALAMAN BERIKUTNYA (BATAS CETAK)";
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #f3f4f6;
            padding: 0 12px;
            font-size: 10px;
            color: #4b5563;
            font-weight: bold;
            letter-spacing: 0.1em;
        }
    </style>
</head>
<body class="bg-gray-100 text-black p-8">

    @php
        $reports = isset($isBulk) && $isBulk ? $reports : [[
            'student' => $student,
            'school' => $school,
            'principal' => $principal,
            'activeYear' => $activeYear,
            'rombel' => $rombel,
            'groupedSubjects' => $groupedSubjects,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpha' => $alpha,
            'ekskuls' => $ekskuls,
            'cocurriculars' => $cocurriculars,
            'p5Project' => $p5Project,
            'graduateProfiles' => $graduateProfiles,
            'rank' => $rank,
            'catatanWalikelas' => $catatanWalikelas,
        ]];
    @endphp

    @php
        $selectedPaper = request('paper_size', 'a4');
        $selectedMargin = request('margin_size', 'normal');
    @endphp

    <!-- Opsi & Pengaturan Cetak (No Print Panel) -->
    <div class="max-w-4xl mx-auto mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-4 flex-wrap">
            <!-- Ukuran Kertas -->
            <div class="flex items-center gap-2">
                <label for="paper_size" class="text-xs font-bold text-gray-700 uppercase tracking-wide">Ukuran Kertas:</label>
                <select id="paper_size" onchange="updatePageSetup()" class="bg-white border border-gray-300 text-gray-700 text-xs rounded p-2 focus:ring-blue-500 focus:border-blue-500 font-semibold cursor-pointer">
                    <option value="a4" {{ $selectedPaper === 'a4' ? 'selected' : '' }}>A4 (210 x 297 mm)</option>
                    <option value="f4" {{ $selectedPaper === 'f4' ? 'selected' : '' }}>F4 / Folio (215 x 330 mm)</option>
                </select>
            </div>
            <!-- Margin Setup -->
            <div class="flex items-center gap-2">
                <label for="margin_size" class="text-xs font-bold text-gray-700 uppercase tracking-wide">Margin:</label>
                <select id="margin_size" onchange="updatePageSetup()" class="bg-white border border-gray-300 text-gray-700 text-xs rounded p-2 focus:ring-blue-500 focus:border-blue-500 font-semibold cursor-pointer">
                    <option value="normal" {{ $selectedMargin === 'normal' ? 'selected' : '' }}>Normal (10mm)</option>
                    <option value="sedang" {{ $selectedMargin === 'sedang' ? 'selected' : '' }}>Sedang (7mm)</option>
                    <option value="sempit" {{ $selectedMargin === 'sempit' ? 'selected' : '' }}>Sempit (5mm)</option>
                    <option value="none" {{ $selectedMargin === 'none' ? 'selected' : '' }}>Tanpa Margin</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-xs transition">
                Tutup Halaman
            </button>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded text-xs inline-flex items-center shadow-md transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Rapor
            </button>
        </div>
    </div>

    @foreach($reports as $reportIndex => $report)
        @php
            $student = $report['student'];
            $school = $report['school'];
            $principal = $report['principal'];
            $activeYear = $report['activeYear'];
            $rombel = $report['rombel'];
            $groupedSubjects = $report['groupedSubjects'];
            $sakit = $report['sakit'];
            $izin = $report['izin'];
            $alpha = $report['alpha'];
            $ekskuls = $report['ekskuls'];
            $cocurriculars = $report['cocurriculars'];
            $p5Project = $report['p5Project'];
            $graduateProfiles = $report['graduateProfiles'];
            $rank = $report['rank'];
            $catatanWalikelas = $report['catatanWalikelas'];

            $levelName = $rombel && $rombel->level ? $rombel->level->nama_tingkatan : '';
            $fase = 'A';
            if (preg_match('/\d+/', $levelName, $matches)) {
                $levelNum = (int)$matches[0];
                if ($levelNum >= 5) {
                    $fase = 'C';
                } elseif ($levelNum >= 3) {
                    $fase = 'B';
                }
            }
            $semesterNum = $activeYear ? (strtolower($activeYear->semester) === 'ganjil' ? '1' : (strtolower($activeYear->semester) === 'genap' ? '2' : $activeYear->semester)) : '-';
        @endphp

        @if($reportIndex > 0)
            <div class="page-break"></div>
        @endif

        <!-- PAGE 1 OF STUDENT REPORT -->
        <div class="max-w-4xl print:max-w-full print:w-full print:px-6 print:py-0 mx-auto bg-white p-10 shadow-lg print-container mb-6 page-block">

        <!-- Detail Siswa & Sekolah -->
        <div class="flex justify-between items-start text-xs mb-0">
            <div class="w-3/5">
                <table class="w-full">
                    <tr class="align-top">
                        <td class="py-0.5 w-24 text-gray-700">Nama Murid</td>
                        <td class="w-3 text-center py-0.5">:</td>
                        <td class="py-0.5 uppercase">{{ $student->user->name }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">NIS/NISN</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $student->nis ?: '-' }} / {{ $student->nisn }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Sekolah</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $school->name ?? 'SDN JOMIN TIMUR I' }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Alamat</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5 text-xs leading-tight text-gray-800">{{ $school->address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="w-2/5 flex justify-end">
                <table class="w-auto">
                    <tr class="align-top">
                        <td class="py-0.5 w-28 text-gray-700">Kelas</td>
                        <td class="w-3 text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $rombel ? $rombel->nama_rombel : '-' }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Fase</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $fase }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Semester</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $semesterNum }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Tahun Ajaran</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $activeYear ? $activeYear->tahun_ajaran : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Garis Bawah -->
        <hr class="border-t border-black mb-3 mt-0">

        <!-- Judul Laporan Hasil Belajar -->
        <h2 class="text-sm font-bold text-center uppercase tracking-wider mb-4">LAPORAN HASIL BELAJAR</h2>

        <!-- Tabel A: Capaian Hasil Belajar -->
        <div class="mb-4">
            <table class="w-full text-sm table-custom border-collapse">
                <thead class="bg-gray-50 text-center font-bold">
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-48">Mata Pelajaran</th>
                        <th class="w-24">Nilai Akhir</th>
                        <th>Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedSubjects as $groupName => $subjects)
                        @php $globalIndex = 1; @endphp
                        <tr class="group-header">
                            <td colspan="4" class="font-bold px-3 py-1 text-xs tracking-wider text-gray-700">
                                {{ $groupName }}
                            </td>
                        </tr>
                        @foreach($subjects as $sub)
                            <tr>
                                <td class="text-center">{{ $globalIndex++ }}</td>
                                <td>{{ $sub['nama'] }}</td>
                                <td class="text-center">
                                    {{ $sub['nilai'] ?? '-' }}
                                </td>
                                <td class="text-justify leading-normal text-xs">
                                    {{ $sub['deskripsi'] }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 italic text-gray-400">Belum ada nilai yang diinput untuk siswa ini pada periode aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Seksi B: Projek Kokurikuler -->
        <div class="mb-4">
            <table class="w-full text-xs table-custom border-collapse">
                <thead>
                    <tr class="bg-gray-50 font-bold">
                        <th class="py-0.5 text-center px-4 tracking-wide text-xs">Kokurikuler</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="p-2 text-justify leading-normal text-xs space-y-0.5">
                            @php
                                $cocurricularName = $p5Project ? $p5Project->name : 'Lingkungan Sekitar';
                            @endphp
                            <p>
                                Pada semester ini, ananda menunjukkan capaian yang baik dalam penguatan profil lulusan, yang ditunjukkan melalui kegiatan kokurikuler {{ $cocurricularName }}.
                            </p>
                            @if(!empty($graduateProfiles))
                                @foreach($graduateProfiles as $dimensi => $subdimensis)
                                    <p>
                                        Pada dimensi {{ $dimensi }}, ananda cakap dalam subdimensi {{ implode(', ', $subdimensis) }}.
                                    </p>
                                @endforeach
                            @else
                                <p>
                                    Pada dimensi keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa, ananda cakap dalam subdimensi hubungan dengan sesama manusia.
                                </p>
                                <p>
                                    Pada dimensi kemandirian, ananda cakap dalam subdimensi bertanggung jawab.
                                </p>
                                <p>
                                    Pada dimensi kolaborasi, ananda cakap dalam subdimensi kerja sama.
                                </p>
                                <p>
                                    Pada dimensi kesehatan, ananda cakap dalam subdimensi kebugaran, kesehatan fisik, dan kesehatan mental, kesehatan lingkungan.
                                </p>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Block -->
        <div class="print-footer w-full">
            <!-- Garis Atas -->
            <hr class="border-t border-black my-2">
            <div class="flex justify-between text-black px-2" style="font-size: 8pt !important; font-style: italic !important; font-weight: bold !important;">
                <div>
                    {{ $rombel ? $rombel->nama_rombel : '-' }} | {{ strtoupper($student->user->name) }} | {{ $student->nis ?? $student->nisn }}
                </div>
                <div>
                    Halaman : 1
                </div>
            </div>
        </div>

        </div> <!-- Closes Page 1 Container -->

        <!-- Page Break -->
        <div class="page-break"></div>

        <!-- PAGE 2 OF STUDENT REPORT -->
        <div class="max-w-4xl print:max-w-full print:w-full print:px-6 print:py-0 mx-auto bg-white p-10 shadow-lg print-container page-block">

        <!-- Detail Siswa & Sekolah (Page 2 Header) -->
        <div class="flex justify-between items-start text-xs mb-0">
            <div class="w-3/5">
                <table class="w-full">
                    <tr class="align-top">
                        <td class="py-0.5 w-24 text-gray-700">Nama Murid</td>
                        <td class="w-3 text-center py-0.5">:</td>
                        <td class="py-0.5 uppercase">{{ $student->user->name }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">NIS/NISN</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $student->nis ?: '-' }} / {{ $student->nisn }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Sekolah</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $school->name ?? 'SDN JOMIN TIMUR I' }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Alamat</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5 text-xs leading-tight text-gray-800">{{ $school->address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="w-2/5 flex justify-end">
                <table class="w-auto">
                    <tr class="align-top">
                        <td class="py-0.5 w-28 text-gray-700">Kelas</td>
                        <td class="w-3 text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $rombel ? $rombel->nama_rombel : '-' }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Fase</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $fase }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Semester</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $semesterNum }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-0.5 text-gray-700">Tahun Ajaran</td>
                        <td class="text-center py-0.5">:</td>
                        <td class="py-0.5">{{ $activeYear ? $activeYear->tahun_ajaran : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Garis Bawah -->
        <hr class="border-t border-black mb-4 mt-0">

        <!-- Tabel C: Ekstrakurikuler -->
        <div class="mb-6">
            <table class="w-full text-sm table-custom border-collapse">
                <thead class="bg-gray-50 text-center font-bold">
                    <tr>
                        <th class="w-12">No</th>
                        <th class="w-64">Kegiatan Ekstrakurikuler</th>
                        <th class="w-24">Predikat</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ekskuls as $index => $ekskul)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $ekskul['nama'] }}</td>
                            <td class="text-center">{{ $ekskul['nilai'] }}</td>
                            <td class="text-xs leading-normal">{{ $ekskul['deskripsi'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 italic text-gray-400">Tidak mengikuti kegiatan ekstrakurikuler.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Ketidakhadiran & Catatan Wali Kelas (Side-by-Side Table Layout) -->
        <table class="w-full mb-6" style="border: none !important; table-layout: fixed; border-collapse: collapse;">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 50%;">
            </colgroup>
            <tbody>
                <tr style="border: none !important;">
                    <!-- D. Ketidakhadiran -->
                    <td style="border: none !important; padding: 0 12px 0 0; vertical-align: top;">
                        <table class="w-full text-sm table-custom border-collapse" style="table-layout: fixed;">
                            <thead>
                                <tr class="bg-gray-50 font-bold" style="height: 32px;">
                                    <th colspan="2" class="py-1.5 text-center px-4 tracking-wide text-xs">Ketidakhadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="height: 30px;">
                                    <td style="width: 70%; height: 30px; padding: 4px 8px; vertical-align: middle;">Sakit (S)</td>
                                    <td class="text-center" style="width: 30%; height: 30px; padding: 4px 8px; vertical-align: middle;">{{ $sakit }} hari</td>
                                </tr>
                                <tr style="height: 30px;">
                                    <td style="height: 30px; padding: 4px 8px; vertical-align: middle;">Izin (I)</td>
                                    <td class="text-center" style="height: 30px; padding: 4px 8px; vertical-align: middle;">{{ $izin }} hari</td>
                                </tr>
                                <tr style="height: 30px;">
                                    <td style="height: 30px; padding: 4px 8px; vertical-align: middle;">Tanpa Keterangan (A)</td>
                                    <td class="text-center" style="height: 30px; padding: 4px 8px; vertical-align: middle;">{{ $alpha }} hari</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <!-- E. Catatan Wali Kelas -->
                    <td style="border: none !important; padding: 0 0 0 12px; vertical-align: top;">
                        <table class="w-full text-sm table-custom border-collapse" style="table-layout: fixed;">
                            <thead>
                                <tr class="bg-gray-50 font-bold" style="height: 32px;">
                                    <th class="py-1.5 text-center px-4 tracking-wide text-xs">Catatan Wali Kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-3 text-justify leading-normal text-xs bg-white" style="height: 90px; vertical-align: top;">
                                        {{ $catatanWalikelas }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        @php
            $isGenap = $activeYear && strtolower($activeYear->semester) === 'genap';
        @endphp

        @if($isGenap)
            @php
                $showNaik = true;
                $showTidakNaik = false;
                $targetClass = '.......';
                $isLastLevel = false;

                if ($rombel && $rombel->level) {
                    $currentLevelNum = (int)filter_var($rombel->level->nama_tingkatan, FILTER_SANITIZE_NUMBER_INT);
                    if ($currentLevelNum === 6) {
                        $isLastLevel = true;
                    }
                }

                if (isset($isGenerated) && $isGenerated) {
                    $showNaik = (bool)$isNaik;
                    $showTidakNaik = !$isNaik;
                    $targetClass = $kenaikanKelasTo ?: '.......';
                } else {
                    if ($rombel && $rombel->level) {
                        $currentLevelNum = (int)filter_var($rombel->level->nama_tingkatan, FILTER_SANITIZE_NUMBER_INT);
                        if ($currentLevelNum > 0 && $currentLevelNum < 6) {
                            $nextLevelNum = $currentLevelNum + 1;
                            $levelWords = [
                                2 => 'II (Dua)',
                                3 => 'III (Tiga)',
                                4 => 'IV (Empat)',
                                5 => 'V (Lima)',
                                6 => 'VI (Enam)'
                            ];
                            $targetClass = $levelWords[$nextLevelNum] ?? '.......';
                        } elseif ($currentLevelNum === 6) {
                            $targetClass = 'SMP / Sederajat';
                        }
                    }
                }
            @endphp
            <!-- Keterangan Kenaikan Kelas / Kelulusan -->
            <div class="mb-6">
                <table class="w-full text-sm table-custom border-collapse">
                    <tbody>
                        <tr>
                            <td class="p-3 text-center font-bold text-sm bg-white leading-normal">
                                @if($isLastLevel)
                                    Keterangan Kelulusan : 
                                    <span class="text-sm border-b border-black border-dashed pb-0.5 px-4">
                                        @if($showNaik && !$showTidakNaik)
                                            <span class="font-bold">Lulus</span> / <span style="text-decoration: line-through; opacity: 0.5;">Tinggal</span>
                                        @elseif(!$showNaik && $showTidakNaik)
                                            <span style="text-decoration: line-through; opacity: 0.5;">Lulus</span> / <span class="font-bold">Tinggal</span>
                                        @else
                                            Lulus / Tinggal
                                        @endif
                                    </span>
                                @else
                                    Keterangan Kenaikan Kelas : 
                                    <span class="text-sm border-b border-black border-dashed pb-0.5 px-4">
                                        @if($showNaik && !$showTidakNaik)
                                            <span class="font-bold">Naik</span> / <span style="text-decoration: line-through; opacity: 0.5;">Tidak Naik</span>
                                        @elseif(!$showNaik && $showTidakNaik)
                                            <span style="text-decoration: line-through; opacity: 0.5;">Naik</span> / <span class="font-bold">Tidak Naik</span>
                                        @else
                                            Naik / Tidak Naik
                                        @endif
                                    </span> 
                                    ke kelas 
                                    <span class="text-sm border-b border-black border-dashed pb-0.5 px-4 font-bold">{{ $targetClass }}</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- F. Tanggapan Orang Tua/Wali Murid -->
        <div class="mb-4">
            <table class="w-full text-sm table-custom border-collapse">
                <thead>
                    <tr class="bg-gray-50 font-bold">
                        <th class="py-1 text-center px-4 uppercase tracking-wide text-xs">Tanggapan Orang Tua / Wali Murid</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="p-3 bg-white">
                            <div class="space-y-4 py-1.5">
                                <div class="border-b border-black border-dashed opacity-25"></div>
                                <div class="border-b border-black border-dashed opacity-25"></div>
                                <div class="border-b border-black border-dashed opacity-25"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan Block -->
        <div class="space-y-12 text-sm px-2">
            <div class="flex justify-between">
                <div class="text-center w-60">
                    <p>&nbsp;</p>
                    <p class="mb-20">Orang Tua / Wali Siswa</p>
                    <p class="font-bold">_________________________</p>
                </div>
                <div class="text-center w-60">
                    <p>................., {{ $activeYear && $activeYear->rapor_date ? \Carbon\Carbon::parse($activeYear->rapor_date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</p>
                    <p class="mb-20">Wali Kelas</p>
                    <p class="font-bold underline">{{ $rombel->waliKelas?->nama_lengkap ?? '.........................................' }}</p>
                    <p>NIP. {{ $rombel->waliKelas?->nip ?? '.........................' }}</p>
                </div>
            </div>
            
            <div class="flex justify-center">
                <div class="text-center w-64 mt-6">
                    <p>Mengetahui,</p>
                    <p class="mb-20">Kepala Sekolah</p>
                    <p class="font-bold underline">{{ $principal?->nama_lengkap ?? '.........................................' }}</p>
                    <p>NIP. {{ $principal?->nip ?? '.........................' }}</p>
                </div>
            </div>
        </div>

        <!-- Footer Block -->
        <div class="print-footer w-full">
            <!-- Garis Atas -->
            <hr class="border-t border-black my-2">
            <div class="flex justify-between text-black px-2" style="font-size: 8pt !important; font-style: italic !important; font-weight: bold !important;">
                <div>
                    {{ $rombel ? $rombel->nama_rombel : '-' }} | {{ strtoupper($student->user->name) }} | {{ $student->nis ?? $student->nisn }}
                </div>
                <div>
                    Halaman : 2
                </div>
            </div>
        </div>

        </div> <!-- Closes Page 2 Container -->

        @endforeach

    <!-- Script Pengaturan Cetak Dinamis -->
    <script>
        function updatePageSetup() {
            const paper = document.getElementById('paper_size').value;
            const margin = document.getElementById('margin_size').value;
            
            let sizeCss = 'A4 portrait';
            if (paper === 'f4') {
                sizeCss = '215mm 330mm portrait';
            }
            
            let marginCss = '10mm';
            if (margin === 'sedang') {
                marginCss = '7mm';
            } else if (margin === 'sempit') {
                marginCss = '5mm';
            } else if (margin === 'none') {
                marginCss = '0mm';
            }
            
            const styleId = 'page-setup-style';
            let styleEl = document.getElementById(styleId);
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = styleId;
                document.head.appendChild(styleEl);
            }
            styleEl.innerHTML = `
                @media print {
                    @page {
                        size: ${sizeCss};
                        margin: ${marginCss};
                    }
                    body {
                        padding: 0 !important;
                    }
                }
            `;
            
            // Adjust container min-height for beautiful screen preview setup
            const containers = document.querySelectorAll('.print-container');
            containers.forEach(container => {
                if (paper === 'f4') {
                    container.style.minHeight = '330mm';
                } else {
                    container.style.minHeight = '297mm';
                }
            });
        }
        
        // Run once on load to set up initial state
        document.addEventListener('DOMContentLoaded', updatePageSetup);
    </script>

</body>
</html>
