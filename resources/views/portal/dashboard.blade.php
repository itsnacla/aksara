@extends('layouts.portal')

@section('content')
@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
@endphp

<div class="space-y-8" id="dashboardContainer">
    {{-- Hero --}}
    <header class="flex flex-col md:flex-row justify-between items-start gap-6">
        <div>
            <p class="text-primary font-semibold tracking-widest uppercase text-xs mb-2">Portal Aksara</p>
            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-2">
                {{ $greeting }},
                <span class="text-gray-400 font-medium">
                    @can('AccessParentPortal') Wali Murid @endcan
                    {{ auth()->user()->name }}
                </span>
            </h1>
            <p class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        @if($academicYear)
        <div class="bg-white px-5 py-3 rounded-2xl border border-gray-100 text-xs">
            <span class="text-gray-400 font-semibold uppercase tracking-wider">Tahun Ajaran</span>
            <p class="font-bold text-gray-800 mt-0.5">{{ $academicYear->tahun_ajaran }} — Semester {{ $academicYear->semester }}</p>
        </div>
        @endif
    </header>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="statsRow">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-green-200 transition-colors">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold uppercase">Hadir</span>
            </div>
            <span class="text-2xl font-bold text-gray-800" id="stat-hadir">{{ $attendanceStats['hadir'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-blue-200 transition-colors">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold uppercase">Izin</span>
            </div>
            <span class="text-2xl font-bold text-gray-800" id="stat-izin">{{ $attendanceStats['izin'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-orange-200 transition-colors">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold uppercase">Sakit</span>
            </div>
            <span class="text-2xl font-bold text-gray-800" id="stat-sakit">{{ $attendanceStats['sakit'] }}</span>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-red-200 transition-colors">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="text-xs text-gray-400 font-semibold uppercase">Alpa</span>
            </div>
            <span class="text-2xl font-bold text-gray-800" id="stat-alpa">{{ $attendanceStats['alpa'] }}</span>
        </div>
    </div>

    @can('AccessParentPortal')
    {{-- PARENT DASHBOARD --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Children Status --}}
            <div>
                <h2 class="text-lg font-bold mb-4">Status Anak Hari Ini</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($children as $child)
                    @php $childAtt = $child->attendances->first(); @endphp
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary/30 transition-colors">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-full bg-primary/10 flex items-center justify-center text-primary overflow-hidden shrink-0">
                                @if($child->user->photo)
                                    <img src="{{ asset('storage/' . $child->user->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="font-bold text-sm">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm truncate">{{ $child->user->name }}</h3>
                                <p class="text-[11px] text-gray-400">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl mb-3 text-sm">
                            <span class="text-gray-500">Presensi</span>
                            @if($childAtt)
                                <span class="font-bold {{ $childAtt->status === 'hadir' ? 'text-green-600' : ($childAtt->status === 'alpa' ? 'text-red-500' : 'text-orange-500') }}">
                                    {{ strtoupper($childAtt->status) }}
                                </span>
                            @else
                                <span class="text-gray-300 italic text-xs">Belum Presensi</span>
                            @endif
                        </div>
                        @php
                            $hasRapor = $activeYearId ? \App\Models\StudentRapor::where('student_id', $child->id)->where('academic_year_id', $activeYearId)->where('is_published', true)->exists() : false;
                        @endphp
                        @if($hasRapor)
                            <a href="{{ route('print.rapor', $child) }}" target="_blank" class="block w-full text-center bg-primary hover:bg-primary/90 text-white py-2.5 rounded-xl font-semibold text-xs transition-colors">
                                Lihat E-Raport
                            </a>
                        @else
                            <div class="w-full text-center bg-gray-50 text-gray-400 py-2.5 rounded-xl text-xs font-medium">
                                Rapor Belum Dipublikasikan
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- E-Raport Section for Parents --}}
            <div>
                <h2 class="text-lg font-bold mb-4">E-Raport Digital</h2>
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Rapor Akademik</p>
                            <p class="text-[11px] text-gray-400">Unduh dan tinjau rapor putra-putri Anda</p>
                        </div>
                    </div>
                    @php
                        $allYears = \App\Models\AcademicYear::orderBy('id', 'desc')->get();
                    @endphp
                    <div class="space-y-4">
                        @foreach($children as $child)
                        <div class="border border-gray-100 rounded-xl p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <span class="text-xs font-bold">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ $child->user->name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php $hasAnyRapor = false; @endphp
                                @foreach($allYears as $yr)
                                    @php
                                        $rapor = \App\Models\StudentRapor::where('student_id', $child->id)->where('academic_year_id', $yr->id)->where('is_published', true)->first();
                                    @endphp
                                    @if($rapor)
                                        @php $hasAnyRapor = true; @endphp
                                        <a href="{{ route('print.rapor', ['student' => $child, 'academic_year_id' => $yr->id]) }}" target="_blank"
                                            class="flex items-center justify-between p-3 bg-gray-50 hover:bg-primary/5 rounded-lg transition-colors group">
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-700 group-hover:text-primary transition-colors">{{ $yr->tahun_ajaran }}</p>
                                                <p class="text-[10px] text-gray-400">Semester {{ $yr->semester }}</p>
                                            </div>
                                            <div class="flex items-center gap-1 text-primary shrink-0">
                                                <span class="text-[10px] font-semibold">Lihat</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </div>
                                        </a>
                                    @endif
                                @endforeach
                                @if(!$hasAnyRapor)
                                    <p class="col-span-full text-xs text-gray-400 italic py-2">Belum ada rapor yang dipublikasikan.</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Riwayat Izin --}}
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Riwayat Izin</h2>
                    <a href="{{ route('leaves.index') }}" class="text-primary text-xs font-semibold hover:underline">Lihat Semua →</a>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    @php
                        $recentLeaves = \App\Models\StudentLeave::whereIn('student_id', $children->pluck('id'))->with('student.user')->latest()->take(5)->get();
                    @endphp
                    @forelse($recentLeaves as $leave)
                    <div class="flex items-center justify-between p-4 border-b border-gray-50 last:border-0">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $leave->student->user->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $leave->reason }} · {{ $leave->start_date->format('d M') }}–{{ $leave->end_date->format('d M') }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase shrink-0 ml-3
                            {{ $leave->status === 'approved' ? 'bg-green-50 text-green-700' : ($leave->status === 'pending' ? 'bg-orange-50 text-orange-600' : 'bg-red-50 text-red-600') }}">
                            {{ $leave->status }}
                        </span>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-400 text-sm italic">Belum ada riwayat izin.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Actions --}}
            <div class="bg-primary rounded-2xl p-6 text-white">
                <h3 class="font-bold text-sm mb-4 uppercase tracking-wider opacity-80">Layanan Cepat</h3>
                <div class="space-y-2.5">
                    <a href="{{ route('leaves.create') }}" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-3.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-sm font-semibold">Ajukan Izin Baru</span>
                    </a>
                    <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-3.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="text-sm font-semibold">Daftar Perizinan</span>
                    </a>
                </div>
            </div>

            {{-- Nilai Terbaru --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100">
                <h3 class="font-bold text-sm mb-4">Nilai Terbaru</h3>
                <div class="space-y-3">
                    @forelse($recentGrades as $grade)
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-primary truncate">{{ $grade->subject->nama_mapel }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $grade->student->user->name }}</p>
                        </div>
                        <span class="bg-gray-50 text-gray-800 font-bold text-sm w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                            {{ $grade->nilai_uts ?? $grade->nilai_tugas ?? 0 }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-400 text-xs italic text-center py-2">Belum ada nilai.</p>
                    @endforelse
                </div>
            </div>

            {{-- Ekskul --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100">
                <h3 class="font-bold text-sm mb-4">Ekstrakurikuler</h3>
                <div class="space-y-2.5">
                    @forelse($extracurriculars->take(5) as $ekskul)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="min-w-0">
                            <p class="font-semibold text-xs truncate">{{ $ekskul->nama_ekskul }}</p>
                            <p class="text-[10px] text-gray-400">{{ $ekskul->pembina ?? 'N/A' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase shrink-0 {{ $ekskul->kategori === 'wajib' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                            {{ ucwords($ekskul->kategori) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-400 text-xs italic text-center py-2">Belum ada ekskul.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('AccessStudentPortal')
    {{-- STUDENT DASHBOARD --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Presensi + Rombel --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-sm">Presensi Hari Ini</h3>
                    </div>
                    @if($attendance)
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $attendance->status === 'hadir' ? 'bg-green-50 text-green-700' : 'bg-orange-50 text-orange-600' }}">
                                {{ strtoupper($attendance->status) }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $attendance->created_at->format('H:i') }} WIB</span>
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">Belum ada catatan hari ini.</p>
                    @endif
                </div>
                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="font-bold text-sm">Rombel Aktif</h3>
                    </div>
                    <p class="font-bold text-primary text-sm">{{ $studyGroup->nama_rombel ?? 'Belum Terdaftar' }}</p>
                    @if($studyGroup && $studyGroup->waliKelas)
                        <p class="text-[11px] text-gray-400 mt-1">Wali Kelas: {{ $studyGroup->waliKelas->user->name ?? '-' }}</p>
                    @endif
                </div>
            </div>

            {{-- Jadwal Hari Ini --}}
            <div>
                <h2 class="text-lg font-bold mb-4">Jadwal Hari Ini</h2>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    @forelse($todaySchedules as $schedule)
                    <div class="flex items-center gap-4 p-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                        <div class="text-center shrink-0 w-16">
                            <p class="text-xs font-bold text-primary">{{ $schedule->startTimeSlot?->waktu_mulai?->format('H:i') }}</p>
                            <p class="text-[10px] text-gray-300">{{ $schedule->endTimeSlot?->waktu_selesai?->format('H:i') }}</p>
                        </div>
                        <div class="w-px h-8 bg-gray-200 shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-sm truncate">{{ $schedule->subject?->nama_mapel ?? '-' }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $schedule->teacher?->user?->name ?? '-' }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <p class="text-gray-400 text-sm italic">Tidak ada jadwal hari ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Ekskul --}}
            <div>
                <h2 class="text-lg font-bold mb-4">Ekstrakurikuler</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse($extracurriculars as $ekskul)
                    <div class="bg-white rounded-xl p-4 border border-gray-100 flex items-center justify-between hover:border-primary/20 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-primary/5 rounded-lg flex items-center justify-center text-primary shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-xs truncate">{{ $ekskul->nama_ekskul }}</p>
                                <p class="text-[10px] text-gray-400">{{ $ekskul->pembina ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase shrink-0 ml-2 {{ $ekskul->kategori === 'wajib' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                            {{ ucwords($ekskul->kategori) }}
                        </span>
                    </div>
                    @empty
                    <p class="col-span-full text-gray-400 text-sm italic text-center py-6">Belum ada ekskul.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Kehadiran Ring --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 text-center">
                <h3 class="font-bold text-sm mb-4">Kehadiran Bulan Ini</h3>
                <div class="relative w-28 h-28 mx-auto mb-4">
                    <svg class="w-28 h-28 transform -rotate-90" viewBox="0 0 36 36">
                        <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#f3f4f6" stroke-width="3"/>
                        <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#005da7" stroke-width="3" stroke-dasharray="{{ $attendancePercentage }}, 100" stroke-linecap="round" class="transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xl font-bold text-gray-800">{{ $attendancePercentage }}%</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Persentase kehadiran</p>
            </div>

            {{-- Nilai Terbaru --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100">
                <h3 class="font-bold text-sm mb-4">Nilai Terbaru</h3>
                <div class="space-y-3">
                    @forelse($recentGrades as $grade)
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-xs truncate">{{ $grade->subject->nama_mapel }}</p>
                            <p class="text-[10px] text-gray-400">Tugas / UTS</p>
                        </div>
                        <span class="bg-primary/5 text-primary font-bold text-sm w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                            {{ $grade->nilai_uts ?? $grade->nilai_tugas ?? 0 }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-400 text-xs italic text-center py-2">Belum ada nilai.</p>
                    @endforelse
                </div>
                @if($gradeAverage > 0)
                <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center">
                    <span class="text-xs text-gray-400 font-medium">Rata-rata</span>
                    <span class="font-bold text-primary text-lg">{{ $gradeAverage }}</span>
                </div>
                @endif
            </div>

            {{-- E-Raport --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">E-Raport Digital</h3>
                        <p class="text-[10px] text-gray-400">Rapor akademik Anda</p>
                    </div>
                </div>
                @php
                    $studentSelf = auth()->user()->student;
                    $allYears = \App\Models\AcademicYear::orderBy('id', 'desc')->get();
                @endphp
                <div class="space-y-2">
                    @php $hasAnyRapor = false; @endphp
                    @foreach($allYears as $yr)
                        @php
                            $rapor = $studentSelf ? \App\Models\StudentRapor::where('student_id', $studentSelf->id)->where('academic_year_id', $yr->id)->where('is_published', true)->first() : null;
                        @endphp
                        @if($rapor)
                            @php $hasAnyRapor = true; @endphp
                            <a href="{{ route('print.rapor', ['student' => $studentSelf, 'academic_year_id' => $yr->id]) }}" target="_blank"
                                class="flex items-center justify-between p-3 bg-gray-50 hover:bg-primary/5 rounded-xl transition-colors group">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 group-hover:text-primary transition-colors">{{ $yr->tahun_ajaran }}</p>
                                    <p class="text-[10px] text-gray-400">Semester {{ $yr->semester }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-primary">
                                    <span class="text-[10px] font-semibold">Lihat</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </div>
                            </a>
                        @endif
                    @endforeach
                    @if(!$hasAnyRapor)
                        <div class="text-center py-3">
                            <p class="text-xs text-gray-400 italic">Belum ada rapor yang dipublikasikan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>

{{-- Real-time Polling --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    function pollData() {
        fetch('{{ route("dashboard.realtime") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.attendanceStats) {
                ['hadir','izin','sakit','alpa'].forEach(k => {
                    const el = document.getElementById('stat-' + k);
                    if (el) {
                        const newVal = data.attendanceStats[k] || 0;
                        if (el.textContent.trim() !== String(newVal)) {
                            el.textContent = newVal;
                            el.style.transition = 'transform 0.3s';
                            el.style.transform = 'scale(1.15)';
                            setTimeout(() => el.style.transform = 'scale(1)', 300);
                        }
                    }
                });
            }
        })
        .catch(() => {});
    }
    setInterval(pollData, 15000);
});
</script>
@endsection
