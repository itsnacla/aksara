<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - {{ $school->name ?? 'Aksara' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @php
            $w = ($paperSize === 'f4') ? '215mm' : '210mm';
            $h = ($paperSize === 'f4') ? '330mm' : '297mm';
            
            if ($orientation === 'landscape') {
                $paperWidth = $h;
                $paperHeight = $w;
                $pageSize = $paperWidth . ' ' . $paperHeight;
            } else {
                $paperWidth = $w;
                $paperHeight = $h;
                $pageSize = $paperWidth . ' ' . $paperHeight;
            }
        @endphp

        @page {
            size: {{ $pageSize }};
            margin: 5mm;
        }

        @media print {
            body { background-color: white !important; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            .paper-container { 
                box-shadow: none !important; 
                margin: 0 !important; 
                border: none !important;
                width: 100% !important;
            }
        }

        body { 
            font-family: 'Arial Narrow', Arial, sans-serif; 
            font-size: 8.5px; 
            line-height: 1.1;
            background-color: #4b5563; /* Dark background to see paper edges */
            padding: 20px 0;
        }
        
        .paper-container {
            background-color: white;
            margin: 0 auto;
            padding: 10mm;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            width: {{ $paperWidth }};
            min-height: {{ $paperHeight }};
            position: relative;
            box-sizing: border-box;
        }
        
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 1.5px; text-align: center; word-wrap: break-word; }
        
        .bg-istirahat { background-color: #f3f4f6 !important; font-weight: bold; font-size: 7px; }
        .vertical-middle { vertical-align: middle; }
        
        .subject-name { font-weight: bold; font-size: 8.5px; display: block; }
        .teacher-code { font-size: 7.5px; color: #374151; display: block; border-top: 1px solid rgba(0,0,0,0.1); margin-top: 1px; }
        
        .header-title { font-size: 12px; font-weight: bold; }
        .header-subtitle { font-size: 10px; font-weight: bold; }
    </style>
</head>
<body class="bg-gray-100 p-0">

    <!-- Action Buttons -->
    <div class="max-w-7xl mx-auto px-4 mb-4 flex justify-end gap-3 no-print">
        <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 shadow-lg">Tutup</button>
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded font-bold shadow-lg text-sm hover:bg-blue-700">Cetak Laporan</button>
    </div>

    @foreach($rombels as $rombelIndex => $rombel)
    <div class="paper-container {{ !$loop->last ? 'page-break mb-8' : '' }}">
        
        <!-- Header -->
        <div class="text-center mb-2">
            <h1 class="header-title uppercase">JADWAL PELAJARAN {{ $studyGroupId === 'all' ? '' : '' }}</h1>
            <h2 class="header-subtitle uppercase">{{ $school->name ?? 'NAMA SEKOLAH' }}</h2>
            <h3 class="text-[10px] font-bold uppercase">TAHUN PELAJARAN {{ $rombel->academicYear->tahun_ajaran ?? '-' }} - SEMESTER {{ strtoupper($rombel->academicYear->semester ?? '-') }}</h3>
            @if($studyGroupId !== 'all')
                <h4 class="text-xs font-bold mt-0.5">KELAS / ROMBEL: {{ $rombel->nama_rombel }}</h4>
            @endif
        </div>

        <table>
            <thead>
                <tr class="bg-gray-100 font-bold uppercase text-[8px]">
                    <th class="w-14">Kelas</th>
                    <th class="w-8">Jam</th>
                    <th class="w-16">Waktu</th>
                    @php $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']; @endphp
                    @foreach($days as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php 
                    $timeSlots = \App\Models\TimeSlot::whereHas('levels', fn($q) => $q->where('levels.id', $rombel->level_id))
                        ->orderBy('urutan')->get();
                    $schedules = $groupedSchedules->get($rombel->id) ?? collect();
                    $rowSpan = $timeSlots->count();
                @endphp
                
                @foreach($timeSlots as $index => $slot)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $rowSpan }}" class="font-bold bg-gray-50 vertical-middle text-xs">
                                {{ $rombel->nama_rombel }}
                            </td>
                        @endif
                        
                        <td class="{{ $slot->is_istirahat ? 'bg-istirahat' : '' }} text-[8px]">
                            {{ $slot->is_istirahat ? '-' : $slot->nama_jam }}
                        </td>
                        
                        <td class="{{ $slot->is_istirahat ? 'bg-istirahat text-[7px]' : 'text-[7px]' }}">
                            {{ $slot->waktu_mulai->format('H:i') }}-{{ $slot->waktu_selesai->format('H:i') }}
                        </td>

                        @foreach($days as $day)
                            @php
                                $item = $schedules->where('hari', $day)
                                    ->filter(fn($s) => $s->startTimeSlot->urutan <= $slot->urutan && $s->endTimeSlot->urutan >= $slot->urutan)
                                    ->first();
                                
                                $bgColor = '';
                                if ($item && !$slot->is_istirahat) {
                                    $colors = ['#fee2e2', '#ffedd5', '#fef9c3', '#dcfce7', '#d1fae5', '#e0f2fe', '#e0e7ff', '#f3e8ff', '#fae8ff'];
                                    $colorIndex = crc32($item->subject->nama_mapel) % count($colors);
                                    $bgColor = $colors[abs($colorIndex)];
                                }
                            @endphp
                            
                            <td style="background-color: {{ $slot->is_istirahat ? '#f3f4f6' : ($bgColor ?: 'transparent') }};" 
                                class="{{ $slot->is_istirahat ? 'bg-istirahat' : '' }}">
                                @if($slot->is_istirahat)
                                    <span class="text-[7px] tracking-widest font-black opacity-30">ISTIRAHAT</span>
                                @elseif($item)
                                    <span class="subject-name">
                                        {{ $showSubjectCode ? ($item->subject->kode_mapel ?? $item->subject->nama_mapel) : $item->subject->nama_mapel }}
                                    </span>
                                    @if($showTeacherCode)
                                        <span class="teacher-code">
                                            {{ $item->teacher->kode_guru ?? $item->teacher->user->name }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer / Signatures -->
        <div class="mt-4 flex justify-between px-8 text-[8px]">
            <div class="text-center w-32">
                <p>Mengetahui,</p>
                <p class="mb-8">Kepala Sekolah</p>
                <p class="font-bold underline">{{ $principal?->nama_lengkap ?? '................................' }}</p>
                <p>NIP. {{ $principal?->nip ?? '........................' }}</p>
            </div>
            <div class="text-center w-32">
                <p>{{ $school->city ?? 'Banyuwangi' }}, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="mb-8">Wali Kelas,</p>
                <p class="font-bold underline">{{ $rombel->waliKelas?->nama_lengkap ?? '................................' }}</p>
                <p>NIP. {{ $rombel->waliKelas->nip ?? '........................' }}</p>
            </div>
        </div>

        <!-- Legend (Daftar Kode) -->
        <div class="border-t pt-1.5 mt-2">
            <div class="grid grid-cols-2 gap-4 text-[7.5px]">
                <!-- Legend Mapel -->
                <div>
                    <p class="font-bold border-b mb-1 uppercase text-[8px]">Keterangan Mata Pelajaran:</p>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                        @php
                            $usedSubjectIds = $schedules->pluck('subject_id')->unique();
                            $legendSubjects = \App\Models\Subject::whereIn('id', $usedSubjectIds)->orderBy('kode_mapel')->get();
                        @endphp
                        @foreach($legendSubjects as $ls)
                            <div class="flex leading-tight">
                                <span class="font-bold w-20 shrink-0 whitespace-nowrap">{{ $ls->kode_mapel ?? '??' }}</span>
                                <span class="flex-1">: {{ $ls->nama_mapel }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!-- Legend Guru -->
                @if($showTeacherCode)
                <div>
                    <p class="font-bold border-b mb-1 uppercase text-[8px]">Keterangan Guru:</p>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                        @php
                            $usedTeacherIds = $schedules->pluck('teacher_id')->unique();
                            $legendTeachers = \App\Models\Teacher::with('user')->whereIn('id', $usedTeacherIds)->get();
                        @endphp
                        @foreach($legendTeachers as $lt)
                            <div class="flex leading-tight">
                                <span class="font-bold w-20 shrink-0 whitespace-nowrap">{{ $lt->kode_guru ?? '??' }}</span>
                                <span class="flex-1">: {{ $lt->nama_lengkap }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach

</body>
</html>
