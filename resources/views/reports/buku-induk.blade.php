<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Induk - {{ $student->user->name ?? 'Siswa' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 13px;
            line-height: 1.4;
        }
        
        /* A4 Size Emulation for Screen */
        .page-container {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin: 30px auto;
            width: 21cm;
            height: 29.7cm;
            padding: 1.5cm 1.5cm;
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 4px 6px;
            text-align: center;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .row-data {
            display: flex;
            margin-bottom: 2px;
        }
        .label-num { width: 25px; text-align: right; margin-right: 8px; }
        .label-text { width: 160px; }
        .label-colon { width: 15px; }
        .label-val { flex: 1; }
        
        .sub-row { padding-left: 33px; display: flex; margin-bottom: 2px; }
        .sub-num { width: 20px; }
        .sub-text { width: 140px; }
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
            Cetak Buku Induk
        </button>
    </div>

    @foreach ($records as $index => $data)
        @php
            $student = $data['student'];
            $school = $data['school'];
            $principal = $data['principal'];
            $rombel = $data['rombel'];
            
            // Format Data
            $studentName = ucwords(strtolower($student->user->name ?? ''));
            $nis = $student->nis ?? '-';
            $nisn = $student->nisn ?? '-';
            $gender = $student->gender == 'L' ? 'Laki-laki' : ($student->gender == 'P' ? 'Perempuan' : '-');
            $pob = $student->pob ?? '-';
            $dob = $student->dob ? \Carbon\Carbon::parse($student->dob)->locale('id')->translatedFormat('d F Y') : '-';
            $religion = $student->religion ?? '-';
            $familyStatus = $student->status_dalam_keluarga ?? '-';
            $anakKe = $student->anak_ke ?? '-';
            $address = $student->address ?? '-';
            $phone = $student->phone ?? '-';
            
            $parent = $student->parent;
            $father = trim(preg_replace('/^(bapak|bpk\.|bpk)\s+/i', '', $student->ayah_nama ?: ($parent->father_name ?? '-')));
            $mother = trim(preg_replace('/^(ibu|ibuk|ibu\.|ibuk\.)\s+/i', '', $student->ibu_nama ?: ($parent->mother_name ?? '-')));
            $fatherJob = $parent->father_occupation ?? '-';
            $motherJob = $parent->mother_occupation ?? '-';
            $parentAddress = $parent->address ?? '-';
            $parentPhone = $parent->no_whatsapp ?? '-';
            
            $guardian = $student->wali_nama ?? '-';
            $guardianJob = $student->wali_pekerjaan ?? '-';
            $guardianAddress = $student->wali_alamat ?? '-';
            $guardianPhone = $student->wali_telepon ?? '-';
            
            $schoolOrigin = $student->previous_school ?? '-';
            $acceptedClass = $rombel ? str_replace('Kelas ', '', $rombel->level->nama_tingkatan) : '-';
            $acceptedDate = $student->created_at ? \Carbon\Carbon::parse($student->created_at)->locale('id')->translatedFormat('d F Y') : '-';
            $admissionYear = $student->created_at ? \Carbon\Carbon::parse($student->created_at)->locale('id')->translatedFormat('Y') : '-';
            
            $signatureLocation = ucwords(strtolower($school->village ?? 'Kota'));
            $signatureDate = now()->locale('id')->translatedFormat('d F Y');
        @endphp

        <!-- PAGE 1: KETERANGAN SISWA -->
        <div class="page-container" style="justify-content: flex-start; padding-top: 1rem;">
            <div class="text-center mb-4">
                <h1 class="text-lg font-bold tracking-wide underline">Buku Induk Siswa {{ $school->name ?? 'Sekolah' }}</h1>
            </div>
            
            <div class="flex justify-between font-bold mb-6 text-[15px]">
                <div>Nomor Induk : {{ $nis }}</div>
                <div>NISN : {{ $nisn }}</div>
                <div>Thn Masuk : {{ $admissionYear }}</div>
            </div>

            <div class="font-bold mb-2">A. KETERANGAN SISWA</div>
            
            <div class="relative">
                <div class="w-3/4">
                    <div class="row-data">
                        <div class="label-num">1.</div>
                        <div class="label-text">Nama Siswa</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $studentName }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">2.</div>
                        <div class="label-text">Jenis Kelamin</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $gender }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">3.</div>
                        <div class="label-text">Kelahiran</div>
                        <div class="label-colon"></div>
                        <div class="label-val"></div>
                    </div>
                    <div class="sub-row">
                        <div class="sub-num">a.</div>
                        <div class="sub-text">Tempat</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $pob }}</div>
                    </div>
                    <div class="sub-row">
                        <div class="sub-num">b.</div>
                        <div class="sub-text">Tanggal</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $dob }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">4.</div>
                        <div class="label-text">Agama</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $religion }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">5.</div>
                        <div class="label-text">Status dalam Keluarga</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $familyStatus }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">6.</div>
                        <div class="label-text">Anak ke</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $anakKe }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">7.</div>
                        <div class="label-text">Alamat</div>
                        <div class="label-colon">:</div>
                        <div class="label-val pr-4">{{ $address }}</div>
                    </div>
                    <div class="row-data">
                        <div class="label-num">8.</div>
                        <div class="label-text">Nomor Telepon</div>
                        <div class="label-colon">:</div>
                        <div class="label-val">{{ $phone }}</div>
                    </div>
                </div>
                
                <!-- Photo Box -->
                <div class="absolute right-0 top-0 w-[3cm] h-[4cm] bg-gray-200 border border-gray-300 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
            </div>

            <div class="font-bold mt-4 mb-2">B. KETERANGAN ORANG TUA/WALI SISWA</div>
            
            <div class="row-data">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Nama Orangtua</div>
                <div class="label-colon"></div>
                <div class="label-val"></div>
            </div>
            <div class="sub-row">
                <div class="sub-num">a.</div>
                <div class="sub-text">Ayah</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $father }}</div>
            </div>
            <div class="sub-row">
                <div class="sub-num">b.</div>
                <div class="sub-text">Ibu</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $mother }}</div>
            </div>
            
            <div class="row-data">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Pekerjaan Orangtua</div>
                <div class="label-colon"></div>
                <div class="label-val"></div>
            </div>
            <div class="sub-row">
                <div class="sub-num">a.</div>
                <div class="sub-text">Ayah</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $fatherJob }}</div>
            </div>
            <div class="sub-row">
                <div class="sub-num">b.</div>
                <div class="sub-text">Ibu</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $motherJob }}</div>
            </div>
            
            <div class="row-data" style="margin-top: 4px;">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Alamat Orangtua</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $parentAddress }}</div>
            </div>
            <div class="row-data" style="margin-top: 4px;">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Telepon Orangtua</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $parentPhone }}</div>
            </div>
            
            <div class="row-data mt-2">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Wali Siswa</div>
                <div class="label-colon"></div>
                <div class="label-val"></div>
            </div>
            <div class="sub-row">
                <div class="sub-num">a.</div>
                <div class="sub-text">Nama</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $guardian }}</div>
            </div>
            <div class="sub-row">
                <div class="sub-num">b.</div>
                <div class="sub-text">Pekerjaan</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $guardianJob }}</div>
            </div>
            <div class="sub-row">
                <div class="sub-num">c.</div>
                <div class="sub-text">Alamat</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $guardianAddress }}</div>
            </div>
            <div class="sub-row">
                <div class="sub-num">d.</div>
                <div class="sub-text">Telepon</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $guardianPhone }}</div>
            </div>

            <div class="font-bold mt-4 mb-2">C. PERKEMBANGAN SISWA</div>
            
            <div class="row-data">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Sekolah Asal</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $schoolOrigin }}</div>
            </div>
            <div class="row-data mt-1">
                <div class="label-text" style="padding-left: 25px; width: 193px;">Diterima di sekolah ini</div>
                <div class="label-colon"></div>
                <div class="label-val"></div>
            </div>
            <div class="sub-row">
                <div class="sub-num">a.</div>
                <div class="sub-text">Dikelas</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $acceptedClass }}</div>
            </div>
            <div class="sub-row">
                <div class="sub-num">b.</div>
                <div class="sub-text">Pada Tanggal</div>
                <div class="label-colon">:</div>
                <div class="label-val">{{ $acceptedDate }}</div>
            </div>

            <!-- Signature Section -->
            <div class="mt-4 flex justify-between absolute bottom-12 w-[calc(100%-3cm)]">
                <div class="w-1/2"></div>
                <div class="w-1/2 text-left" style="padding-left: 2rem;">
                    <p class="mb-1">{{ $signatureLocation }}, {{ $signatureDate }}</p>
                    <p class="mb-14">Kepala Sekolah</p>
                    <p class="font-bold underline">{{ $principal?->nama_lengkap ?? '.........................................' }}</p>
                    <p class="font-bold">NIP. {{ $principal?->nip ?? '.........................................' }}</p>
                </div>
            </div>
        </div>
        
        @php
            $chunkedData = $data['chunkedData'] ?? [];
        @endphp

        @foreach($chunkedData as $chunkIndex => $chunkData)
        <!-- PAGE 2: HASIL BELAJAR SISWA -->
        <div class="page-container" style="justify-content: flex-start; padding-top: 2rem;">
            @if($chunkIndex === 0)
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold tracking-wide">Hasil Belajar Siswa</h1>
            </div>
            @endif
            
            <table class="w-full text-sm font-bold border-2 border-black">
                <tr>
                    <td colspan="2" class="text-left bg-gray-100" style="width: 55%;">NAMA : {{ $studentName }}</td>
                    @foreach($chunkData['tahun_ajarans'] as $ta)
                    <td colspan="2" class="bg-gray-100 w-[15%]">{{ $ta }}</td>
                    @endforeach
                    @for($i = count($chunkData['tahun_ajarans']); $i < 3; $i++)
                    <td colspan="2" class="bg-gray-100 w-[15%]">........ / ........</td>
                    @endfor
                </tr>
                <tr>
                    <td colspan="2" class="text-left bg-gray-100">NISN : {{ $nisn }}</td>
                    @for($i = 0; $i < 3; $i++)
                    <td colspan="2" class="bg-gray-100">SEMESTER</td>
                    @endfor
                </tr>
                <tr>
                    <td colspan="2" class="text-left bg-gray-100">NIS : {{ $nis }}</td>
                    @for($i = 0; $i < 3; $i++)
                    <td class="bg-gray-100 w-[7.5%]">Ganjil</td>
                    <td class="bg-gray-100 w-[7.5%]">Genap</td>
                    @endfor
                </tr>
                <tr>
                    <td class="bg-gray-100 w-10">NO</td>
                    <td class="bg-gray-100">MATA PELAJARAN</td>
                    @for($i = 0; $i < 6; $i++)
                    <td class="bg-gray-100">NILAI</td>
                    @endfor
                </tr>
                
                @foreach($chunkData['subjects'] as $i => $subject)
                <tr class="font-normal">
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $subject['nama'] }}</td>
                    @foreach($chunkData['tahun_ajarans'] as $ta)
                    <td>{{ $subject['grades'][$ta]['ganjil'] ?? '-' }}</td>
                    <td>{{ $subject['grades'][$ta]['genap'] ?? '-' }}</td>
                    @endforeach
                    @for($j = count($chunkData['tahun_ajarans']); $j < 3; $j++)
                    <td>-</td>
                    <td>-</td>
                    @endfor
                </tr>
                @endforeach
            </table>
            
            <table class="w-full text-sm font-bold border-2 border-black mt-4">
                <tr>
                    <td class="bg-gray-100 w-10">NO</td>
                    <td class="bg-gray-100" style="width: calc(55% - 40px);">EKSTRAKURIKULER</td>
                    @for($i = 0; $i < 6; $i++)
                    <td class="bg-gray-100 w-[7.5%]">NILAI</td>
                    @endfor
                </tr>
                @forelse($chunkData['ekskuls'] as $i => $eks)
                <tr class="font-normal">
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $eks['nama'] }}</td>
                    @foreach($chunkData['tahun_ajarans'] as $ta)
                    <td>{{ $eks['grades'][$ta]['ganjil'] ?? '-' }}</td>
                    <td>{{ $eks['grades'][$ta]['genap'] ?? '-' }}</td>
                    @endforeach
                    @for($j = count($chunkData['tahun_ajarans']); $j < 3; $j++)
                    <td>-</td>
                    <td>-</td>
                    @endfor
                </tr>
                @empty
                <tr class="font-normal">
                    <td colspan="8" class="text-center py-2 text-gray-500">Tidak ada data ekstrakurikuler</td>
                </tr>
                @endforelse
            </table>
            
            <table class="w-full text-sm font-bold border-2 border-black mt-4">
                <tr>
                    <td class="bg-gray-100 w-10">NO</td>
                    <td class="bg-gray-100" style="width: calc(55% - 40px);">KETIDAKHADIRAN</td>
                    @for($i = 0; $i < 6; $i++)
                    <td class="bg-gray-100 w-[7.5%]">HARI</td>
                    @endfor
                </tr>
                @php $attTypes = ['Sakit' => 'sakit', 'Izin' => 'izin', 'Tanpa Keterangan' => 'alpha']; @endphp
                @php $idx = 1; @endphp
                @foreach($attTypes as $label => $key)
                <tr class="font-normal">
                    <td>{{ $idx++ }}.</td>
                    <td class="text-left">{{ $label }}</td>
                    @foreach($chunkData['tahun_ajarans'] as $ta)
                    <td>{{ $chunkData['attendances'][$key][$ta]['ganjil'] ?? '-' }}</td>
                    <td>{{ $chunkData['attendances'][$key][$ta]['genap'] ?? '-' }}</td>
                    @endforeach
                    @for($j = count($chunkData['tahun_ajarans']); $j < 3; $j++)
                    <td>-</td>
                    <td>-</td>
                    @endfor
                </tr>
                @endforeach
            </table>

            <!-- Signature Section -->
            <div class="mt-4 flex justify-end">
                <div class="w-64 text-left mr-8">
                    <p class="mb-1">{{ $signatureLocation }}, {{ $signatureDate }}</p>
                    <p class="mb-16">Kepala Sekolah</p>
                    <p class="font-bold underline">{{ $principal?->nama_lengkap ?? '.........................................' }}</p>
                    <p class="font-bold">NIP. {{ $principal?->nip ?? '.........................................' }}</p>
                </div>
            </div>
        </div>
        @endforeach
    @endforeach
</body>
</html>
