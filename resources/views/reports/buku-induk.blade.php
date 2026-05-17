<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Induk Siswa - {{ isset($isBulk) && $isBulk ? 'Cetak Massal' : $student->user->name }}</title>
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
            padding: 2.2cm 1.8cm;
            position: relative;
            box-sizing: border-box;
        }
        
        .page-border {
            border: 4px double #000;
            height: 100%;
            padding: 1.2cm 1cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
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
                padding: 1.8cm 1.5cm !important;
                width: 21cm !important;
                height: 29.7cm !important;
                page-break-after: always !important;
                break-after: page !important;
                display: block !important;
                position: relative !important;
            }
            .page-border {
                border: 4px double #000 !important;
                height: 100% !important;
                padding: 1cm 0.8cm !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
        }
        
        /* Form Table Grid Elements */
        .dotted-line {
            border-bottom: 1px dotted #000;
            flex-grow: 1;
            margin-left: 8px;
        }
        .form-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 0.25rem;
        }
        .form-label {
            font-weight: normal;
        }
        
        /* Clean and formal tables */
        .formal-table th, .formal-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: left;
            vertical-align: middle;
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
            Cetak Buku Induk
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
        @endphp

        <!-- ======================================= -->
        <!-- PAGE 1: COVER PAGE (HALAMAN SAMPUL)     -->
        <!-- ======================================= -->
        <div class="page-container">
            <div class="page-border flex flex-col justify-between items-center text-center p-8">
                <div>
                    <h1 class="text-2xl font-bold uppercase tracking-widest mt-6">Buku Induk Siswa</h1>
                    <h2 class="text-3xl font-bold uppercase tracking-wider mt-2">Sekolah Dasar</h2>
                    <span class="text-2xl font-bold uppercase tracking-widest px-4 py-1 border border-black rounded inline-block mt-4">SD</span>
                </div>

                <!-- Logo Section -->
                <div class="my-10 flex flex-col items-center justify-center">
                    @if($school && $school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo Sekolah" class="w-36 h-36 object-contain">
                    @else
                        <div class="w-36 h-36 border-2 border-dashed border-black flex items-center justify-center rounded">
                            <span class="text-xs uppercase font-bold text-center p-4">Logo Sekolah / Lambang Garuda</span>
                        </div>
                    @endif
                </div>

                <!-- Boxed Student Identity -->
                <div class="w-full max-w-lg border border-black p-6 bg-white text-left font-bold uppercase tracking-wide space-y-4 my-8">
                    <div class="flex items-center">
                        <span class="w-48 text-sm">Nama Peserta Didik</span>
                        <span class="mr-3">:</span>
                        <span class="flex-grow border-b border-black text-sm pb-0.5 underline font-bold">{{ $student->user->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-48 text-sm">NISN / NIS</span>
                        <span class="mr-3">:</span>
                        <span class="flex-grow border-b border-black text-sm pb-0.5">{{ $student->nisn ?: '-' }} / {{ $student->nis ?: '-' }}</span>
                    </div>
                </div>

                <!-- Footer Ministry -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold uppercase tracking-wider">Kementerian Pendidikan Dasar Dan Menengah</h3>
                    <h4 class="text-xl font-bold uppercase tracking-widest mt-1">Republik Indonesia</h4>
                </div>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 2: KETERANGAN TENTANG SEKOLAH     -->
        <!-- ======================================= -->
        <div class="page-container">
            <div class="page-border flex flex-col justify-between p-8">
                <div class="w-full">
                    <h2 class="text-xl font-bold text-center uppercase tracking-wide border-b-2 border-black pb-2 mb-10">Keterangan Tentang Sekolah</h2>
                    
                    <div class="space-y-6 text-base md:text-lg">
                        <div class="flex items-center">
                            <span class="w-8">1.</span>
                            <span class="w-64">Nama Sekolah</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow font-bold border-b border-black pb-0.5">{{ $school->name ?? 'SDN JOMIN TIMUR I' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">2.</span>
                            <span class="w-64">NPSN</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->npsn ?? '20236253' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">3.</span>
                            <span class="w-64">Alamat Sekolah</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->address ?? 'Dusun Karajan' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">4.</span>
                            <span class="w-64">Kelurahan / Desa</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->village ?? 'Jomin Timur' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">5.</span>
                            <span class="w-64">Kecamatan</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->district ?? 'Kec. Kotabaru' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">6.</span>
                            <span class="w-64">Kota / Kabupaten</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->city ?? 'Kab. Karawang' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">7.</span>
                            <span class="w-64">Provinsi</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->province ?? 'Prov. Jawa Barat' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">8.</span>
                            <span class="w-64">Website</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->website ?? 'http://' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-8">9.</span>
                            <span class="w-64">E-mail</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $school->email ?? 'sdnjomintimur1@yahoo.co.id' }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-16 self-end w-64 pr-4">
                    <p class="mb-24"></p>
                </div>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 3: IDENTITAS PESERTA DIDIK         -->
        <!-- ======================================= -->
        <div class="page-container">
            <div class="page-border flex flex-col justify-between p-6">
                <div>
                    <h2 class="text-lg font-bold text-center uppercase tracking-wide border-b-2 border-black pb-2 mb-6">Identitas Peserta Didik</h2>
                    
                    <div class="space-y-2 text-xs md:text-sm">
                        <!-- 1 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">1.</span>
                            <span class="w-56">Nama Lengkap Peserta Didik</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black font-bold uppercase pb-0.5">{{ $student->user->name }}</span>
                        </div>
                        <!-- 2 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">2.</span>
                            <span class="w-56">Nomor Induk / NISN</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</span>
                        </div>
                        <!-- 3 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">3.</span>
                            <span class="w-56">Tempat, Tanggal Lahir</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->pob ?: '-' }}, {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <!-- 4 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">4.</span>
                            <span class="w-56">Jenis Kelamin</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->gender === 'L' ? 'Laki-Laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}</span>
                        </div>
                        <!-- 5 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">5.</span>
                            <span class="w-56">Agama</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ ucfirst($student->religion) ?: '-' }}</span>
                        </div>
                        <!-- 6 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">6.</span>
                            <span class="w-56">Status dalam Keluarga</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->lives_with_parent ? 'Anak Kandung' : '-' }}</span>
                        </div>
                        <!-- 7 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">7.</span>
                            <span class="w-56">Anak ke</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->anak_ke ?: '-' }}</span>
                        </div>
                        <!-- 8 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">8.</span>
                            <span class="w-56">Alamat Peserta Didik</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->address ?: '-' }}</span>
                        </div>
                        <!-- 9 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">9.</span>
                            <span class="w-56">Nomor Telepon Rumah</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->phone ?: '-' }}</span>
                        </div>
                        <!-- 10 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">10.</span>
                            <span class="w-56">Sekolah Asal</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->previous_school ?: '-' }}</span>
                        </div>
                        <!-- 11 -->
                        <div class="flex items-start flex-col pl-6">
                            <div class="flex w-full items-start">
                                <span class="w-52">11. Diterima di sekolah ini</span>
                            </div>
                            <div class="flex w-full items-start pl-4 mt-1">
                                <span class="w-48">a. Di kelas</span>
                                <span class="mr-3">:</span>
                                <span class="flex-grow border-b border-black pb-0.5">{{ $rombel && $rombel->level ? $rombel->level->nama_tingkatan : 'I' }}</span>
                            </div>
                            <div class="flex w-full items-start pl-4 mt-1">
                                <span class="w-48">b. Pada tanggal</span>
                                <span class="mr-3">:</span>
                                <span class="flex-grow border-b border-black pb-0.5">{{ $student->created_at ? \Carbon\Carbon::parse($student->created_at)->translatedFormat('d F Y') : '-' }}</span>
                            </div>
                        </div>
                        <!-- 12 -->
                        <div class="flex items-start flex-col pl-6">
                            <div class="flex w-full items-start">
                                <span class="w-52">12. Nama Orang Tua</span>
                            </div>
                            <div class="flex w-full items-start pl-4 mt-1">
                                <span class="w-48">a. Ayah</span>
                                <span class="mr-3">:</span>
                                <span class="flex-grow border-b border-black pb-0.5">{{ $student->ayah_nama ?: ($student->parent->father_name ?? '-') }}</span>
                            </div>
                            <div class="flex w-full items-start pl-4 mt-1">
                                <span class="w-48">b. Ibu</span>
                                <span class="mr-3">:</span>
                                <span class="flex-grow border-b border-black pb-0.5">{{ $student->ibu_nama ?: ($student->parent->mother_name ?? '-') }}</span>
                            </div>
                        </div>
                        <!-- 13 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">13.</span>
                            <span class="w-56">Alamat Orang Tua</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->ayah_nama ? ($student->address ?: '-') : ($student->parent->address ?? '-') }}</span>
                        </div>
                        <!-- 14 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">14.</span>
                            <span class="w-56">Nomor Telepon Rumah</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->phone ?: ($student->parent->phone ?? '-') }}</span>
                        </div>
                        <!-- 15 -->
                        <div class="flex items-start flex-col pl-6">
                            <div class="flex w-full items-start">
                                <span class="w-52">15. Pekerjaan Orang Tua</span>
                            </div>
                            <div class="flex w-full items-start pl-4 mt-1">
                                <span class="w-48">a. Ayah</span>
                                <span class="mr-3">:</span>
                                <span class="flex-grow border-b border-black pb-0.5">{{ $student->ayah_pekerjaan ?: ($student->parent->father_occupation ?? '-') }}</span>
                            </div>
                            <div class="flex w-full items-start pl-4 mt-1">
                                <span class="w-48">b. Ibu</span>
                                <span class="mr-3">:</span>
                                <span class="flex-grow border-b border-black pb-0.5">{{ $student->ibu_pekerjaan ?: ($student->parent->mother_occupation ?? '-') }}</span>
                            </div>
                        </div>
                        <!-- 16 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">16.</span>
                            <span class="w-56">Nama Wali Siswa</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->wali_nama ?: ($student->parent->guardian_name ?? '-') }}</span>
                        </div>
                        <!-- 17 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">17.</span>
                            <span class="w-56">Alamat Wali Peserta Didik</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->wali_nama ? ($student->address ?: '-') : '-' }}</span>
                        </div>
                        <!-- 18 -->
                        <div class="flex items-start">
                            <span class="w-6 font-semibold">18.</span>
                            <span class="w-56">Pekerjaan Wali Peserta Didik</span>
                            <span class="mr-3">:</span>
                            <span class="flex-grow border-b border-black pb-0.5">{{ $student->wali_pekerjaan ?: ($student->parent->guardian_occupation ?? '-') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Signature Block -->
                <div class="mt-8 flex justify-between items-end text-sm">
                    <div class="w-28 h-36 border border-black flex items-center justify-center text-xs text-gray-500 italic text-center font-bold px-2 bg-gray-50">
                        Pas Foto<br>3 x 4
                    </div>
                    
                    <div class="text-center w-72">
                        <p class="text-xs mb-1">{{ $school->district ?? 'Kotabaru' }}, {{ $student->created_at ? \Carbon\Carbon::parse($student->created_at)->translatedFormat('d F Y') : '-' }}</p>
                        <p class="text-xs font-bold mb-14">Kepala Sekolah</p>
                        <p class="font-bold underline text-sm">{{ $principal->user->name ?? '.........................................' }}</p>
                        <p class="text-xs mt-0.5">NIP. {{ $principal->nip ?? '.........................................' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 4: PINDAH SEKOLAH - KELUAR          -->
        <!-- ======================================= -->
        <div class="page-container">
            <div class="page-border flex flex-col justify-between p-8">
                <div class="w-full">
                    <h2 class="text-xl font-bold text-center uppercase tracking-wide border-b-2 border-black pb-2 mb-4">Keterangan Pindah Sekolah</h2>
                    <h3 class="text-lg font-bold text-center uppercase tracking-wider mb-6">KELUAR</h3>

                    <div class="mb-6 text-sm font-semibold flex items-center">
                        <span class="w-44">Nama Peserta Didik</span>
                        <span class="mr-3">:</span>
                        <span class="border-b border-black flex-grow pb-0.5 font-bold uppercase">{{ $student->user->name }}</span>
                    </div>

                    <!-- Transfer Out Table -->
                    <table class="w-full formal-table text-xs border border-black">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="w-20 text-center font-bold">Tanggal</th>
                                <th class="w-36 text-center font-bold">Kelas yang ditinggalkan</th>
                                <th class="text-center font-bold">Sebab-sebab Keluar atau Atas Permintaan (Tertulis)</th>
                                <th class="w-64 text-center font-bold">Tanda Tangan Kepala Sekolah, Stempel Sekolah, dan Tanda Tangan Orang Tua/Wali</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Slot 1 -->
                            <tr class="h-36">
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="relative p-2">
                                    <div class="absolute top-2 right-2 text-right">
                                        <p class="text-[10px] mb-8">Kepala Sekolah,</p>
                                        <p class="text-[10px] underline">............................................</p>
                                        <p class="text-[10px]">NIP.</p>
                                    </div>
                                    <div class="absolute bottom-2 left-2 text-left">
                                        <p class="text-[10px] mb-8">Orang Tua/Wali,</p>
                                        <p class="text-[10px] underline">............................................</p>
                                    </div>
                                </td>
                            </tr>
                            <!-- Slot 2 -->
                            <tr class="h-36">
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="relative p-2">
                                    <div class="absolute top-2 right-2 text-right">
                                        <p class="text-[10px] mb-8">Kepala Sekolah,</p>
                                        <p class="text-[10px] underline">............................................</p>
                                        <p class="text-[10px]">NIP.</p>
                                    </div>
                                    <div class="absolute bottom-2 left-2 text-left">
                                        <p class="text-[10px] mb-8">Orang Tua/Wali,</p>
                                        <p class="text-[10px] underline">............................................</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-[10px] text-gray-500 italic mt-4">
                    * Lembar ini diisi secara manual oleh pihak sekolah apabila siswa pindah/keluar dari sekolah.
                </div>
            </div>
        </div>

        <!-- ======================================= -->
        <!-- PAGE 5: PINDAH SEKOLAH - MASUK          -->
        <!-- ======================================= -->
        <div class="page-container">
            <div class="page-border flex flex-col justify-between p-8">
                <div class="w-full">
                    <h2 class="text-xl font-bold text-center uppercase tracking-wide border-b-2 border-black pb-2 mb-4">Keterangan Pindah Sekolah</h2>
                    <h3 class="text-lg font-bold text-center uppercase tracking-wider mb-6">MASUK</h3>

                    <div class="mb-6 text-sm font-semibold flex items-center">
                        <span class="w-44">Nama Peserta Didik</span>
                        <span class="mr-3">:</span>
                        <span class="border-b border-black flex-grow pb-0.5 font-bold uppercase">{{ $student->user->name }}</span>
                    </div>

                    <!-- 3 Entry Slots -->
                    <div class="space-y-4">
                        @for ($slot = 1; $slot <= 3; $slot++)
                            <div class="border border-black p-4 relative h-36 text-xs flex justify-between">
                                <div class="space-y-1.5 w-[55%]">
                                    <div class="flex">
                                        <span class="w-5 font-bold">{{ $slot }}.</span>
                                        <span class="w-32">Nama Siswa</span>
                                        <span class="mr-2">:</span>
                                        <span class="border-b border-black flex-grow">___________________________</span>
                                    </div>
                                    <div class="flex">
                                        <span class="w-5"></span>
                                        <span class="w-32">Nomor Induk</span>
                                        <span class="mr-2">:</span>
                                        <span class="border-b border-black flex-grow">___________________________</span>
                                    </div>
                                    <div class="flex">
                                        <span class="w-5"></span>
                                        <span class="w-32">Nama Sekolah Asal</span>
                                        <span class="mr-2">:</span>
                                        <span class="border-b border-black flex-grow">___________________________</span>
                                    </div>
                                    <div class="flex flex-col pl-5">
                                        <div class="flex w-full mt-0.5">
                                            <span class="w-28 font-bold">Masuk di sekolah ini:</span>
                                        </div>
                                        <div class="flex w-full mt-0.5 pl-2">
                                            <span class="w-26">a. Tanggal</span>
                                            <span class="mr-2">:</span>
                                            <span class="border-b border-black flex-grow">_________________</span>
                                        </div>
                                        <div class="flex w-full mt-0.5 pl-2">
                                            <span class="w-26">b. Di Kelas</span>
                                            <span class="mr-2">:</span>
                                            <span class="border-b border-black flex-grow">_________________</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right w-[40%] flex flex-col justify-between items-end pr-2 pt-1">
                                    <p class="text-[9px]">............................., .......................</p>
                                    <p class="text-[9px] font-bold mb-8">Kepala Sekolah,</p>
                                    <p class="text-[9px] underline">......................................................</p>
                                    <p class="text-[9px]">NIP.</p>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="text-[10px] text-gray-500 italic mt-2">
                    * Lembar ini diisi secara manual oleh pihak sekolah apabila menerima siswa pindahan masuk.
                </div>
            </div>
        </div>
    @endforeach

</body>
</html>
