<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi Siswa</title>
    <!-- Tailwind CSS untuk gaya yang rapi dan konsisten -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                background-color: white !important;
            }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            table { page-break-inside: auto; }
            tr    { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            /* Menghapus shadow dan margin saat print */
            .print-container { 
                box-shadow: none !important; 
                padding: 0 !important; 
                margin: 0 !important; 
                max-width: 100% !important;
            }
        }
        body { font-family: 'Times New Roman', Times, serif; }
        .table-custom th, .table-custom td {
            border: 1px solid #000;
            padding: 8px;
        }
    </style>
</head>
<body class="bg-gray-200 text-black p-8">
    
    <div class="max-w-5xl mx-auto bg-white p-10 shadow-lg print-container">
        
        <!-- Action Buttons (No Print) -->
        <div class="mb-8 flex justify-end gap-3 no-print border-b pb-4">
            <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                Tutup Tab
            </button>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded inline-flex items-center shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>

        <!-- Kop Surat -->
        <div class="flex items-center border-b-4 border-black pb-4 mb-8">
            <div class="w-24">
                @if($school && $school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-full h-auto object-contain">
                @else
                    <img src="{{ asset('images/logo-nobg.png') }}" alt="Logo" class="w-full h-auto object-contain">
                @endif
            </div>
            <div class="flex-1 text-center px-4">
                <h1 class="text-2xl font-bold uppercase tracking-wide">{{ $school->name ?? 'NAMA SEKOLAH' }}</h1>
                <p class="text-sm mt-1">{{ $school->address ?? 'Alamat Sekolah Belum Diatur' }}</p>
                <p class="text-sm">
                    Telp: {{ $school->phone ?? '-' }} | Email: {{ $school->email ?? '-' }}
                </p>
            </div>
            <div class="w-24"></div> <!-- Balancer supaya teks tetap di tengah -->
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold uppercase underline">Rekapitulasi Presensi Siswa</h2>
        </div>

        <!-- Informasi Filter -->
        <div class="flex justify-between mb-6">
            <div>
                <table class="text-sm">
                    <tr><td class="font-bold pr-4">Rombongan Belajar</td><td>: {{ $rombel ? $rombel->nama_rombel : 'Semua Rombel (Data Global)' }}</td></tr>
                    @if($rombel)
                    <tr><td class="font-bold pr-4">Tingkat / Ruangan</td><td>: {{ $rombel->level->nama_tingkatan ?? '-' }} / {{ $rombel->classroom->nama_ruangan ?? '-' }}</td></tr>
                    <tr><td class="font-bold pr-4">Wali Kelas</td><td>: {{ $rombel->waliKelas?->nama_lengkap ?? '-' }}</td></tr>
                    @endif
                </table>
            </div>
            <div>
                <table class="text-sm">
                    <tr><td class="font-bold pr-4">Periode Tanggal</td><td>: 
                        {{ $from ? \Carbon\Carbon::parse($from)->translatedFormat('d F Y') : 'Awal' }} 
                        s/d 
                        {{ $until ? \Carbon\Carbon::parse($until)->translatedFormat('d F Y') : 'Akhir' }}
                    </td></tr>
                    @if($rombel)
                    <tr><td class="font-bold pr-4">Tahun Ajaran</td><td>: {{ $rombel->academicYear->tahun_ajaran ?? '-' }} ({{ ucfirst($rombel->academicYear->semester ?? '-') }})</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Tabel Rekapitulasi -->
        <div class="mb-8">
            <table class="w-full text-sm text-left table-custom border-collapse">
                <thead class="bg-gray-100 text-center font-bold">
                    <tr>
                        <th rowspan="2" class="w-12">No</th>
                        <th rowspan="2" class="w-32">NISN</th>
                        <th rowspan="2">Nama Siswa</th>
                        <th colspan="4" class="py-1">Keterangan</th>
                    </tr>
                    <tr>
                        <th class="w-16 py-1">H</th>
                        <th class="w-16 py-1">S</th>
                        <th class="w-16 py-1">I</th>
                        <th class="w-16 py-1">A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary as $index => $data)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $data['nisn'] }}</td>
                        <td class="px-3">{{ $data['name'] }}</td>
                        <td class="text-center font-semibold text-green-700">{{ $data['hadir'] }}</td>
                        <td class="text-center font-semibold text-yellow-600">{{ $data['sakit'] }}</td>
                        <td class="text-center font-semibold text-blue-600">{{ $data['izin'] }}</td>
                        <td class="text-center font-semibold text-red-600">{{ $data['alpha'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 italic text-gray-500">Tidak ada data presensi yang ditemukan pada kriteria dan periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <p class="text-xs mt-2 italic">*Keterangan: H (Hadir), S (Sakit), I (Izin), A (Alpa)</p>
        </div>

        <!-- Bagian Tanda Tangan -->
        <div class="flex justify-between mt-16 text-sm px-10">
            <div class="text-center w-64">
                <p>&nbsp;</p>
                <p class="mb-20">Mengetahui,<br>Kepala Sekolah</p>
                <p class="font-bold underline">{{ $principal?->nama_lengkap ?? '.........................................' }}</p>
                <p>NIP. {{ $principal?->nip ?? '.........................' }}</p>
            </div>
            <div class="text-center w-64">
                <p>................., {{ now()->translatedFormat('d F Y') }}</p>
                <p class="mb-20">Wali Kelas</p>
                <p class="font-bold underline">{{ $rombel->waliKelas?->nama_lengkap ?? '.........................................' }}</p>
                <p>NIP. {{ $rombel->waliKelas?->nip ?? '.........................' }}</p>
            </div>
        </div>

    </div>

    <script>
        // Opsional: Otomatis memunculkan dialog print saat halaman selesai dimuat
        // window.onload = function() {
        //    setTimeout(function() { window.print(); }, 500);
        // }
    </script>
</body>
</html>
