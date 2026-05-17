<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Induk Siswa - {{ $student->user->name }}</title>
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
                padding: 0 !important; 
                margin: 0 !important; 
                max-width: 100% !important;
            }
        }
        body { font-family: 'Times New Roman', Times, serif; }
        .table-custom th, .table-custom td {
            border: 1px solid #000;
            padding: 6px 12px;
        }
        .form-label {
            width: 35%;
            font-weight: bold;
            vertical-align: top;
            padding: 4px 0;
        }
        .form-value {
            padding: 4px 8px;
            vertical-align: top;
        }
    </style>
</head>
<body class="bg-gray-100 text-black p-8">

    <div class="max-w-4xl mx-auto bg-white p-10 shadow-lg print-container">
        
        <!-- Action Buttons (No Print) -->
        <div class="mb-8 flex justify-end gap-3 no-print border-b pb-4">
            <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded inline-flex items-center transition">
                Tutup Halaman
            </button>
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded inline-flex items-center shadow-md transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Buku Induk
            </button>
        </div>

        <!-- Kop Surat -->
        <div class="flex items-center border-b-4 border-black pb-4 mb-8">
            <div class="w-24 h-24 flex items-center justify-center bg-gray-50 border border-gray-200 rounded">
                @if($school && $school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-20 h-20 object-contain">
                @else
                    <div class="text-xs text-gray-400 font-bold text-center">Aksara Logo</div>
                @endif
            </div>
            <div class="flex-1 text-center px-4">
                <h1 class="text-2xl font-bold uppercase tracking-wide">{{ $school->name ?? 'AKSARA ACADEMIC PORTAL' }}</h1>
                <p class="text-sm mt-1">{{ $school->address ?? 'Alamat Sekolah Belum Diatur' }}</p>
                <p class="text-sm">
                    Telp: {{ $school->phone ?? '-' }} | Email: {{ $school->email ?? '-' }}
                </p>
            </div>
            <div class="w-24"></div>
        </div>

        <!-- Judul Dokumen -->
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold uppercase tracking-wider underline">Lembar Buku Induk Siswa</h2>
            <p class="text-sm font-semibold mt-1">Kelas Aktif: {{ $rombel ? $rombel->nama_rombel : '-' }}</p>
        </div>

        <!-- Lembar Data Induk -->
        <div class="space-y-6">
            
            <!-- SECTION 1: IDENTITAS SISWA -->
            <div>
                <h3 class="text-md font-bold uppercase border-b border-black pb-1 mb-3">A. Identitas Diri Siswa</h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="form-label">1. Nama Lengkap</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value font-semibold">{{ $student->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">2. NISN</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->nisn }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">3. NIS</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->nis ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">4. Nomor Induk Kependudukan (NIK)</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->nik ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">5. Tempat, Tanggal Lahir</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->pob ?: '-' }}, {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">6. Jenis Kelamin</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">7. Agama</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ ucfirst($student->religion) ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">8. No. Akta Kelahiran</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->no_akta_lahir ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">9. Anak Ke / Dari</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->anak_ke ?: '-' }} / {{ $student->jumlah_saudara ? ($student->jumlah_saudara + 1) : '-' }} bersaudara</td>
                    </tr>
                </table>
            </div>

            <!-- SECTION 2: TEMPAT TINGGAL -->
            <div>
                <h3 class="text-md font-bold uppercase border-b border-black pb-1 mb-3">B. Keterangan Tempat Tinggal</h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="form-label">1. Alamat Lengkap</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->address ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">2. Desa / Kelurahan</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->village ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">3. Kecamatan</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->district ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">4. Kota / Kabupaten</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->city ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">5. Provinsi</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->province ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">6. Nomor Telepon / HP</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">7. Tinggal Bersama Orang Tua</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->lives_with_parent ? 'Ya' : 'Tidak' }}</td>
                    </tr>
                </table>
            </div>

            <!-- SECTION 3: KESEHATAN -->
            <div>
                <h3 class="text-md font-bold uppercase border-b border-black pb-1 mb-3">C. Keterangan Jasmani & Kesehatan</h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="form-label">1. Golongan Darah</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->golongan_darah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">2. Tinggi Badan</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->tinggi_badan ? $student->tinggi_badan . ' cm' : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">3. Berat Badan</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->berat_badan ? $student->berat_badan . ' kg' : '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- SECTION 4: ORANG TUA KANDUNG -->
            <div>
                <h3 class="text-md font-bold uppercase border-b border-black pb-1 mb-3">D. Keterangan Orang Tua Kandung</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-bold underline text-sm mb-2">Data Ayah</h4>
                        <table class="w-full text-xs space-y-1">
                            <tr><td class="font-semibold w-24">Nama</td><td>: {{ $student->ayah_nama ?: ($student->parent->father_name ?? '-') }}</td></tr>
                            <tr><td class="font-semibold w-24">NIK</td><td>: {{ $student->ayah_nik ?: '-' }}</td></tr>
                            <tr><td class="font-semibold w-24">Pendidikan</td><td>: {{ $student->ayah_pendidikan ?: '-' }}</td></tr>
                            <tr><td class="font-semibold w-24">Pekerjaan</td><td>: {{ $student->ayah_pekerjaan ?: ($student->parent->father_occupation ?? '-') }}</td></tr>
                            <tr><td class="font-semibold w-24">Penghasilan</td><td>: {{ $student->ayah_penghasilan ?: '-' }}</td></tr>
                        </table>
                    </div>
                    <div>
                        <h4 class="font-bold underline text-sm mb-2">Data Ibu</h4>
                        <table class="w-full text-xs space-y-1">
                            <tr><td class="font-semibold w-24">Nama</td><td>: {{ $student->ibu_nama ?: ($student->parent->mother_name ?? '-') }}</td></tr>
                            <tr><td class="font-semibold w-24">NIK</td><td>: {{ $student->ibu_nik ?: '-' }}</td></tr>
                            <tr><td class="font-semibold w-24">Pendidikan</td><td>: {{ $student->ibu_pendidikan ?: '-' }}</td></tr>
                            <tr><td class="font-semibold w-24">Pekerjaan</td><td>: {{ $student->ibu_pekerjaan ?: ($student->parent->mother_occupation ?? '-') }}</td></tr>
                            <tr><td class="font-semibold w-24">Penghasilan</td><td>: {{ $student->ibu_penghasilan ?: '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: WALI -->
            <div>
                <h3 class="text-md font-bold uppercase border-b border-black pb-1 mb-3">E. Keterangan Wali</h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="form-label">1. Nama Wali</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->wali_nama ?: ($student->parent->guardian_name ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">2. Pekerjaan Wali</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->wali_pekerjaan ?: ($student->parent->guardian_occupation ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="form-label">3. Hubungan Keluarga</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->wali_hubungan ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- SECTION 6: RIWAYAT SEKOLAH -->
            <div>
                <h3 class="text-md font-bold uppercase border-b border-black pb-1 mb-3">F. Riwayat Pendidikan Sebelumnya</h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="form-label">1. Sekolah Asal</td>
                        <td class="w-4 text-center">:</td>
                        <td class="form-value">{{ $student->previous_school ?: '-' }}</td>
                    </tr>
                </table>
            </div>

        </div>

        <!-- Tanda Tangan & Foto -->
        <div class="mt-16 flex justify-between items-end text-sm px-4">
            <div class="w-32 h-40 border border-black flex items-center justify-center text-xs text-gray-400 italic text-center font-bold">
                Pas Foto<br>3 x 4
            </div>
            
            <div class="text-center w-64">
                <p>Mengetahui,</p>
                <p class="mb-20">Kepala Sekolah</p>
                <p class="font-bold underline">{{ $principal->user->name ?? '.........................................' }}</p>
                <p>NIP. {{ $principal->nip ?? '.........................' }}</p>
            </div>
        </div>

    </div>

</body>
</html>
