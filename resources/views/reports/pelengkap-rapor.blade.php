<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelengkap Rapor - {{ isset($isBulk) && $isBulk ? 'Cetak Massal' : $student->user->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        /* A4 Size Emulation for Screen */
        .page-container {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin: 30px auto;
            width: 21cm;
            height: 29.7cm;
            padding: 2cm 2cm;
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        /* Print Styles */
        @media print {
            body {
                background-color: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .page-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 1.5cm 1.5cm !important;
                width: 21cm !important;
                height: 29.7cm !important;
                page-break-after: always !important;
                break-after: page !important;
                display: flex !important;
                position: relative !important;
            }
        }

        .cover-box {
            border: 1px solid #000;
            padding: 6px;
            width: 320px;
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin: 0 auto;
        }
        
        .title-spacing {
            margin: 40px 0;
        }
        
        .formal-table {
            width: 100%;
            border-collapse: collapse;
        }
        .formal-table th, .formal-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }

        /* Lists */
        .identitas-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .identitas-num {
            width: 30px;
        }
        .identitas-label {
            width: 200px;
        }
        .identitas-colon {
            width: 15px;
        }
        .identitas-val {
            flex-grow: 1;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">

    <!-- Action Buttons (Hidden when printing) -->
    <div class="max-w-4xl mx-auto my-6 flex justify-end gap-3 no-print border-b pb-4">
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-5 rounded inline-flex items-center transition shadow-sm text-sm">
            Tutup Halaman
        </button>
        <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-7 rounded inline-flex items-center shadow-md transition text-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak Pelengkap Rapor
        </button>
    </div>

    @php
        $records = isset($isBulk) && $isBulk ? $reports : [[
            'student' => $student,
            'school' => $school,
            'principal' => $principal,
            'rombel' => $rombel,
        ]];
    @endphp

    @foreach ($records as $index => $data)
        @php
            $student = $data['student'];
            $school = $data['school'];
            $principal = $data['principal'];
            $rombel = $data['rombel'];

            $formatAddress = function($address, $village, $district, $city, $province) {
                return $address ?: '-';
            };
            
            $getStudentAddress = function($student) use ($formatAddress) {
                if ($student->address) return $formatAddress($student->address, $student->village, $student->district, $student->city, $student->province);
                if ($student->parent && $student->parent->address) return $formatAddress($student->parent->address, $student->parent->village, $student->parent->district, $student->parent->city, $student->parent->province);
                return '-';
            };
            
            $getParentAddress = function($student) use ($formatAddress) {
                if ($student->parent && $student->parent->address) return $formatAddress($student->parent->address, $student->parent->village, $student->parent->district, $student->parent->city, $student->parent->province);
                if ($student->address) return $formatAddress($student->address, $student->village, $student->district, $student->city, $student->province);
                return '-';
            };
            
            $getStudentPhone = function($student) {
                return $student->phone ?: ($student->parent->no_whatsapp ?? '-');
            };
            
            $getParentPhone = function($student) {
                return $student->parent->no_whatsapp ?? ($student->phone ?: '-');
            };

            $levelMapping = [
                'SD' => 'SEKOLAH DASAR',
                'SMP' => 'SEKOLAH MENENGAH PERTAMA',
                'SMA' => 'SEKOLAH MENENGAH ATAS',
                'SMK' => 'SEKOLAH MENENGAH KEJURUAN',
            ];
            $fullSchoolLevel = $levelMapping[$school->school_level ?? 'SMA'] ?? 'SEKOLAH MENENGAH ATAS';
            $shortSchoolLevel = $school->school_level ?? 'SMA';
        @endphp

        <!-- ======================================= -->
        <!-- PAGE 1: COVER PAGE                      -->
        <!-- ======================================= -->
        <div class="page-container" style="justify-content: space-around; text-align: center;">
            <div style="margin-top: 2rem;">
                @if($school && $school->logo_pemda)
                    <img src="{{ Storage::url($school->logo_pemda) }}" alt="Logo Pemda" class="h-32 object-contain mx-auto">
                @else
                    <div class="h-32 w-32 border border-dashed border-gray-400 mx-auto flex items-center justify-center text-xs text-gray-500">Logo Pemda</div>
                @endif
            </div>

            <div class="title-spacing">
                <h1 class="text-xl font-bold uppercase tracking-wide">{{ $fullSchoolLevel }}</h1>
                <h1 class="text-xl font-bold uppercase tracking-wide mt-1">( {{ $shortSchoolLevel }} )</h1>
            </div>

            <div>
                @if($school && $school->logo)
                    <img src="{{ Storage::url($school->logo) }}" alt="Logo Sekolah" class="h-32 object-contain mx-auto">
                @else
                    <div class="h-32 w-32 border border-dashed border-gray-400 mx-auto flex items-center justify-center text-xs text-gray-500">Logo Sekolah</div>
                @endif
            </div>

            <div class="my-10 space-y-6">
                <div>
                    <div class="text-base font-bold mb-2">Nama Peserta Didik</div>
                    <div class="cover-box capitalize">{{ $student->user->name }}</div>
                </div>
                <div>
                    <div class="text-base font-bold mb-2">NISN / NIS</div>
                    <div class="cover-box">{{ $student->nisn ?: '-' }} / {{ $student->nis ?: '-' }}</div>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <h2 class="text-lg font-bold uppercase tracking-wide">KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h2>
                <h2 class="text-lg font-bold uppercase tracking-wide mt-2">REPUBLIK INDONESIA</h2>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 2: IDENTITAS SEKOLAH               -->
        <!-- ======================================= -->
        <div class="page-container" style="justify-content: flex-start; padding-top: 4rem;">
            <div class="text-center mb-16">
                <h2 class="text-lg font-bold uppercase tracking-wide">{{ $fullSchoolLevel }}</h2>
                <h2 class="text-lg font-bold uppercase tracking-wide mt-1">( {{ $shortSchoolLevel }} )</h2>
            </div>

            <div class="pl-12 pr-8 space-y-4 text-[15px]">
                <div class="flex">
                    <div class="w-48">Nama Sekolah</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->name ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">NPSN</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->npsn ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">NIS/NSS/NDS</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->nis_nss_nds ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">Alamat Sekolah</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->address ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">Kelurahan / Desa</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->village ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">Kecamatan</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->district ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">Kota/Kabupaten</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->city ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">Provinsi</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->province ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">Website</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->website ?? '-' }}</div>
                </div>
                <div class="flex">
                    <div class="w-48">E-mail</div>
                    <div class="w-6">:</div>
                    <div class="flex-grow">{{ $school->email ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 3: IDENTITAS PESERTA DIDIK         -->
        <!-- ======================================= -->
        <div class="page-container" style="justify-content: flex-start; padding-top: 3rem;">
            <div class="text-center mb-10">
                <h2 class="text-lg font-bold uppercase tracking-wide">IDENTITAS PESERTA DIDIK</h2>
            </div>

            <div class="px-8">
                <!-- 1 -->
                <div class="identitas-row">
                    <div class="identitas-num">1.</div>
                    <div class="identitas-label">Nama Lengkap Peserta Didik</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val capitalize">{{ $student->user->name }}</div>
                </div>
                <!-- 2 -->
                <div class="identitas-row">
                    <div class="identitas-num">2.</div>
                    <div class="identitas-label">Nomor Induk/NISN</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</div>
                </div>
                <!-- 3 -->
                <div class="identitas-row">
                    <div class="identitas-num">3.</div>
                    <div class="identitas-label">Tempat ,Tanggal Lahir</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val capitalize">{{ $student->pob ?: '-' }}, {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->locale('id')->translatedFormat('d F Y') : '-' }}</div>
                </div>
                <!-- 4 -->
                <div class="identitas-row">
                    <div class="identitas-num">4.</div>
                    <div class="identitas-label">Jenis Kelamin</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->gender === 'L' ? 'Laki-Laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}</div>
                </div>
                <!-- 5 -->
                <div class="identitas-row">
                    <div class="identitas-num">5.</div>
                    <div class="identitas-label">Agama</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ ucfirst($student->religion) ?: '-' }}</div>
                </div>
                <!-- 6 -->
                <div class="identitas-row">
                    <div class="identitas-num">6.</div>
                    <div class="identitas-label">Status dalam Keluarga</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->lives_with_parent ? 'Anak Kandung' : '-' }}</div>
                </div>
                <!-- 7 -->
                <div class="identitas-row">
                    <div class="identitas-num">7.</div>
                    <div class="identitas-label">Anak ke</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->anak_ke ?: '-' }}</div>
                </div>
                <!-- 8 -->
                <div class="identitas-row">
                    <div class="identitas-num">8.</div>
                    <div class="identitas-label">Alamat Peserta Didik</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $getStudentAddress($student) }}</div>
                </div>
                <!-- 9 -->
                <div class="identitas-row">
                    <div class="identitas-num">9.</div>
                    <div class="identitas-label">Nomor Telepon Rumah</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $getStudentPhone($student) }}</div>
                </div>
                <!-- 10 -->
                <div class="identitas-row">
                    <div class="identitas-num">10.</div>
                    <div class="identitas-label">Sekolah Asal</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val capitalize">{{ $student->previous_school ?: '-' }}</div>
                </div>
                <!-- 11 -->
                <div class="identitas-row">
                    <div class="identitas-num">11.</div>
                    <div class="identitas-label" style="width: 100%;">Diterima di sekolah ini</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px;">
                    <div class="identitas-label" style="width: 200px;">Di kelas</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $rombel && $rombel->level ? $rombel->level->nama_tingkatan : 'X' }}</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px;">
                    <div class="identitas-label" style="width: 200px;">Pada tanggal</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->created_at ? \Carbon\Carbon::parse($student->created_at)->locale('id')->translatedFormat('d F Y') : '-' }}</div>
                </div>
                <!-- 12 -->
                <div class="identitas-row">
                    <div class="identitas-num">12.</div>
                    <div class="identitas-label" style="width: 100%;">Nama Orang Tua</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px;">
                    <div class="identitas-label" style="width: 200px;">a. Ayah</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val capitalize">{{ trim(preg_replace('/^(bapak|bpk\.|bpk)\s+/i', '', $student->ayah_nama ?: ($student->parent->father_name ?? '-'))) }}</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px;">
                    <div class="identitas-label" style="width: 200px;">b. Ibu</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val capitalize">{{ trim(preg_replace('/^(ibu|ibuk|ibu\.|ibuk\.)\s+/i', '', $student->ibu_nama ?: ($student->parent->mother_name ?? '-'))) }}</div>
                </div>
                <!-- 13 -->
                <div class="identitas-row">
                    <div class="identitas-num">13.</div>
                    <div class="identitas-label">Alamat Orang Tua</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $getParentAddress($student) }}</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px; margin-top: -6px;">
                    <div class="identitas-label" style="width: 200px;">Nomor Telepon Rumah</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $getParentPhone($student) }}</div>
                </div>
                <!-- 14 -->
                <div class="identitas-row">
                    <div class="identitas-num">14.</div>
                    <div class="identitas-label" style="width: 100%;">Pekerjaan Orang Tua :</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px;">
                    <div class="identitas-label" style="width: 200px;">a. Ayah</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->ayah_pekerjaan ?: ($student->parent->father_occupation ?? '-') }}</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px;">
                    <div class="identitas-label" style="width: 200px;">b. Ibu</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->ibu_pekerjaan ?: ($student->parent->mother_occupation ?? '-') }}</div>
                </div>
                <!-- 15 -->
                <div class="identitas-row">
                    <div class="identitas-num">15.</div>
                    <div class="identitas-label">Nama Wali Siswa</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val capitalize">{{ $student->wali_nama ?: ($student->parent->guardian_name ?? '') }}</div>
                </div>
                <!-- 16 -->
                <div class="identitas-row">
                    <div class="identitas-num">16.</div>
                    <div class="identitas-label">Alamat Wali Peserta Didik</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->wali_nama ? $getParentAddress($student) : '' }}</div>
                </div>
                <div class="identitas-row" style="padding-left: 30px; margin-top: -6px;">
                    <div class="identitas-label" style="width: 200px;">Nomor Telepon Rumah</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->wali_nama ? $getParentPhone($student) : '' }}</div>
                </div>
                <!-- 17 -->
                <div class="identitas-row">
                    <div class="identitas-num">17.</div>
                    <div class="identitas-label">Pekerjaan Wali Peserta Didik</div>
                    <div class="identitas-colon">:</div>
                    <div class="identitas-val">{{ $student->wali_pekerjaan ?: ($student->parent->guardian_occupation ?? '') }}</div>
                </div>
            </div>

            <!-- Bottom Section (Photo & Signature) -->
            <div class="mt-12 -mr-8 flex justify-end items-end gap-12">
                <div class="w-32 h-40 bg-gray-200 border border-gray-300 relative flex items-center justify-center">
                    <!-- Photo placeholder silhoutte like in image -->
                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                
                <div class="text-left w-64">
                    <p class="mb-1 text-[14px]">{{ ucwords(strtolower($school->village ?? 'Desa')) }}, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
                    <p class="mb-16 text-[14px]">Kepala Sekolah</p>
                    <p class="font-bold text-[14px] underline">{{ $principal?->nama_lengkap ?? '.........................................' }}</p>
                    <p class="font-bold text-[14px]">NIP. {{ $principal?->nip ?? '.........................................' }}</p>
                </div>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 4: KETERANGAN PINDAH SEKOLAH (KELUAR) -->
        <!-- ======================================= -->
        <div class="page-container" style="justify-content: flex-start; padding-top: 3rem;">
            <div class="text-center mb-10">
                <h2 class="text-lg font-bold uppercase tracking-wide">KETERANGAN PINDAH SEKOLAH</h2>
            </div>

            <div class="px-2">
                <div class="flex items-center mb-6 text-sm">
                    <span class="w-36">Nama Peserta Didik</span>
                    <span class="mr-3">:</span>
                    <span class="border-b border-black font-bold flex-grow border-dotted border-b-2 border-black/50">{{ $student->user->name }}</span>
                </div>

                <table class="formal-table text-[13px]">
                    <thead>
                        <tr>
                            <th colspan="4" class="text-center uppercase font-bold bg-white">KELUAR</th>
                        </tr>
                        <tr>
                            <th class="text-center w-24">Tanggal</th>
                            <th class="text-center w-36">Kelas yang ditinggalkan</th>
                            <th class="text-center">Sebab-sebab Keluar atau Atas Permintaan (Tertulis)</th>
                            <th class="text-center w-64">Tanda Tangan Kepala Sekolah, Stempel Sekolah, dan Tanda Tangan Orang Tua/Wali</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i=0; $i<3; $i++)
                        <tr>
                            <td class="h-44"></td>
                            <td></td>
                            <td></td>
                            <td class="relative">
                                <div class="absolute top-2 left-2 text-[11px] w-full pr-4">
                                    <p>........................................, ...........</p>
                                    <p>Kepala Sekolah,</p>
                                </div>
                                <div class="absolute top-20 left-2 text-[11px]">
                                    <p>...........................................................</p>
                                    <p>NIP.</p>
                                </div>
                                <div class="absolute bottom-12 left-2 text-[11px]">
                                    <p>Orang Tua/Wali,</p>
                                </div>
                                <div class="absolute bottom-2 left-2 text-[11px]">
                                    <p>...........................................................</p>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 5: KETERANGAN PINDAH SEKOLAH (MASUK) -->
        <!-- ======================================= -->
        <div class="page-container" style="justify-content: flex-start; padding-top: 3rem;">
            <div class="text-center mb-10">
                <h2 class="text-lg font-bold uppercase tracking-wide">KETERANGAN PINDAH SEKOLAH</h2>
            </div>

            <div class="px-2">
                <div class="flex items-center mb-6 text-sm">
                    <span class="w-36">Nama Peserta Didik</span>
                    <span class="mr-3">:</span>
                    <span class="border-b border-black font-bold flex-grow border-dotted border-b-2 border-black/50">{{ $student->user->name }}</span>
                </div>

                <table class="formal-table text-[13px]">
                    <thead>
                        <tr>
                            <th class="text-center w-12 font-bold uppercase">NO</th>
                            <th colspan="2" class="text-center uppercase font-bold">MASUK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i=1; $i<=3; $i++)
                        <tr>
                            <td class="text-center font-bold text-base h-48 align-middle">{{ $i }}.</td>
                            <td class="p-4 align-top border-r-0" style="width: 60%; border-right: none;">
                                <div class="flex mb-3">
                                    <div class="w-32">Nama Siswa</div>
                                    <div class="w-8">________________</div>
                                    <div class="flex-grow">_______________________</div>
                                </div>
                                <div class="flex mb-3">
                                    <div class="w-32">Nomor Induk</div>
                                    <div class="w-8">________________</div>
                                    <div class="flex-grow">_______________________</div>
                                </div>
                                <div class="flex mb-3">
                                    <div class="w-32">Nama Sekolah</div>
                                    <div class="w-8">________________</div>
                                    <div class="flex-grow">_______________________</div>
                                </div>
                                <div class="flex mb-2">
                                    <div class="w-32">Masuk di Sekolah ini:</div>
                                    <div class="flex-grow"></div>
                                </div>
                                <div class="flex mb-2 pl-4">
                                    <div class="w-28">a. Tanggal</div>
                                    <div class="w-8">________________</div>
                                    <div class="flex-grow">_______________________</div>
                                </div>
                                <div class="flex mb-3 pl-4">
                                    <div class="w-28">b. Di Kelas</div>
                                    <div class="w-8">________________</div>
                                    <div class="flex-grow">_______________________</div>
                                </div>
                                <div class="flex mb-1">
                                    <div class="w-32">Tahun Ajaran</div>
                                    <div class="w-8">________________</div>
                                    <div class="flex-grow">_______________________</div>
                                </div>
                            </td>
                            <td class="p-4 align-top relative" style="width: 40%; border-left: none;">
                                <div class="text-[11px]">
                                    <p>........................................, ...........</p>
                                    <p class="mt-2">Kepala Sekolah,</p>
                                </div>
                                <div class="absolute bottom-4 left-4 text-[11px]">
                                    <p>...........................................................</p>
                                    <p>NIP.</p>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

    @endforeach

</body>
</html>
