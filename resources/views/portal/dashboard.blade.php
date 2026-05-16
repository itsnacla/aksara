@extends('layouts.portal')

@section('content')
<div class="space-y-12">
    <!-- Hero Section: Intentional Asymmetry -->
    <header class="flex flex-col md:flex-row justify-between items-start gap-8">
        <div class="max-w-2xl">
            <span class="text-primary font-semibold tracking-widest uppercase text-xs mb-4 block">Selamat Datang di Portal Aksara</span>
            <h1 class="text-5xl md:text-7xl font-bold leading-tight mb-4">
                Selamat pagi, <br/>
                <span class="text-gray-400 italic font-medium">
                    @can('AccessParentPortal')
                        Wali Murid {{ auth()->user()->name }}
                    @else
                        {{ auth()->user()->name }}
                    @endcan
                </span>
            </h1>
            <p class="text-lg text-gray-500 leading-relaxed font-sans">
                @can('AccessParentPortal')
                    Pantau perkembangan dan kehadiran putra-putri Anda dengan mudah di sini.
                @else
                    Informasi sekolah Anda terorganisir di sini. Mari buat hari ini produktif dan tenang.
                @endcan
            </p>
        </div>
    </header>

    @can('AccessParentPortal')
    <!-- Parent Premium Dashboard: Insights & Control -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Attendance Stats Summary -->
        <div class="lg:col-span-2 bg-white rounded-[2rem] p-10 shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-8 opacity-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-8">Statistik Kehadiran Bulan Ini</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-green-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-green-600 mb-1">{{ $attendanceStats['hadir'] }}</span>
                    <span class="text-xs text-green-700 font-semibold uppercase tracking-wider">Hadir</span>
                </div>
                <div class="bg-blue-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-blue-600 mb-1">{{ $attendanceStats['izin'] }}</span>
                    <span class="text-xs text-blue-700 font-semibold uppercase tracking-wider">Izin</span>
                </div>
                <div class="bg-orange-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-orange-600 mb-1">{{ $attendanceStats['sakit'] }}</span>
                    <span class="text-xs text-orange-700 font-semibold uppercase tracking-wider">Sakit</span>
                </div>
                <div class="bg-red-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-red-600 mb-1">{{ $attendanceStats['alpa'] }}</span>
                    <span class="text-xs text-red-700 font-semibold uppercase tracking-wider">Alpa</span>
                </div>
            </div>
        </div>

        <!-- Administrative Actions (Quick Access) -->
        <div class="bg-primary rounded-[2rem] p-10 shadow-lg text-white">
            <h2 class="text-xl font-bold mb-8">Layanan Mandiri</h2>
            <div class="space-y-4">
                <a href="{{ route('leaves.create') }}" class="flex items-center gap-4 bg-white/10 hover:bg-white/20 p-4 rounded-2xl transition-all">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm">Ajukan Izin Baru</span>
                </a>
                <a href="#" class="flex items-center gap-4 bg-white/10 hover:bg-white/20 p-4 rounded-2xl transition-all">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm">Lihat E-Raport</span>
                </a>
                <a href="#" class="flex items-center gap-4 bg-white/10 hover:bg-white/20 p-4 rounded-2xl transition-all">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm">Informasi Tagihan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-12">
            <!-- Monitoring Anak Section -->
            <div class="space-y-6">
                <h2 class="text-2xl font-bold">Status Anak Hari Ini</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($children as $child)
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 group">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary overflow-hidden">
                                @if($child->user->photo)
                                    <img src="{{ asset('storage/' . $child->user->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">{{ $child->user->name }}</h3>
                                <p class="text-xs text-gray-400 uppercase tracking-wider">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-sm p-4 bg-gray-50 rounded-2xl">
                            <span class="text-gray-500 font-medium">Presensi:</span>
                            @php $childAttendance = $child->attendances->first(); @endphp
                            @if($childAttendance)
                                <span class="font-bold {{ $childAttendance->status === 'hadir' ? 'text-green-600' : 'text-orange-500' }}">
                                    {{ strtoupper($childAttendance->status) }}
                                </span>
                            @else
                                <span class="text-gray-300 italic">Belum Presensi</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Activity / Leaves -->
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Riwayat Izin Terbaru</h2>
                    <a href="{{ route('leaves.index') }}" class="text-primary text-sm font-bold hover:underline">Lihat Semua</a>
                </div>
                <div class="bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="p-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Anak</th>
                                <th class="p-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Alasan</th>
                                <th class="p-6 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @php
                                $recentLeaves = \App\Models\StudentLeave::whereIn('student_id', $children->pluck('id'))
                                    ->with('student.user')
                                    ->latest()
                                    ->take(3)
                                    ->get();
                            @endphp
                            @forelse($recentLeaves as $leave)
                            <tr>
                                <td class="p-6">
                                    <span class="font-bold text-sm">{{ $leave->student->user->name }}</span>
                                </td>
                                <td class="p-6">
                                    <span class="text-gray-500 text-sm">{{ $leave->reason }}</span>
                                    <p class="text-[10px] text-gray-300">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</p>
                                </td>
                                <td class="p-6 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $leave->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="p-12 text-center text-gray-400 italic">Belum ada riwayat izin.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Side (Optional context) -->
        <div class="space-y-8">
            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="font-bold text-lg mb-4">Informasi Akademik</h3>
                <div class="space-y-4">
                    @forelse($recentGrades as $grade)
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <p class="text-[10px] text-primary font-bold uppercase tracking-widest mb-1">{{ $grade->subject->nama_pelajaran }}</p>
                        <div class="flex justify-between items-end">
                            <p class="font-bold text-sm">{{ $grade->student->user->name }}</p>
                            <span class="text-xl font-bold">{{ $grade->nilai_uts ?? $grade->nilai_tugas ?? 0 }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-400 text-sm italic text-center p-4">Belum ada nilai.</p>
                    @endforelse
                </div>
            </div>
            
            <div class="bg-orange-50 rounded-[2rem] p-8 border border-orange-100">
                <h3 class="font-bold text-lg text-orange-900 mb-2">Bantuan Cepat</h3>
                <p class="text-sm text-orange-700 mb-6">Butuh bantuan terkait akademik anak Anda?</p>
                <button class="w-full bg-orange-600 text-white py-3 rounded-2xl font-bold text-sm shadow-md">Hubungi Sekolah</button>
            </div>

            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="font-bold text-lg mb-4">Ekstrakurikuler</h3>
                <div class="space-y-4">
                    @forelse($extracurriculars as $ekskul)
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <div class="flex justify-between items-start mb-1">
                            <p class="font-bold text-sm">{{ $ekskul->nama_ekskul }}</p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $ekskul->kategori === 'wajib' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}"
                            >{{ ucwords($ekskul->kategori) }}</span>
                        </div>
                        <p class="text-[10px] text-gray-400">Pembina: {{ $ekskul->pembina ?? 'N/A' }}</p>
                    </div>
                    @empty
                    <p class="text-gray-400 text-sm italic text-center p-4">Belum ada daftar ekskul.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('AccessStudentPortal')
    <!-- Student Insights Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="lg:col-span-2 bg-white rounded-[2rem] p-10 shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold mb-8">Statistik Kehadiran Bulan Ini</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-green-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-green-600 mb-1">{{ $attendanceStats['hadir'] }}</span>
                    <span class="text-xs text-green-700 font-semibold uppercase tracking-wider">Hadir</span>
                </div>
                <div class="bg-blue-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-blue-600 mb-1">{{ $attendanceStats['izin'] }}</span>
                    <span class="text-xs text-blue-700 font-semibold uppercase tracking-wider">Izin</span>
                </div>
                <div class="bg-orange-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-orange-600 mb-1">{{ $attendanceStats['sakit'] }}</span>
                    <span class="text-xs text-orange-700 font-semibold uppercase tracking-wider">Sakit</span>
                </div>
                <div class="bg-red-50 p-6 rounded-3xl text-center">
                    <span class="block text-3xl font-bold text-red-600 mb-1">{{ $attendanceStats['alpa'] }}</span>
                    <span class="text-xs text-red-700 font-semibold uppercase tracking-wider">Alpa</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-10 shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold mb-6">Nilai Terbaru</h2>
            <div class="space-y-6">
                @forelse($recentGrades as $grade)
                <div class="flex items-center justify-between border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                    <div>
                        <p class="font-bold text-sm">{{ $grade->subject->nama_pelajaran }}</p>
                        <p class="text-[10px] text-gray-400">Tugas/UTS</p>
                    </div>
                    <div class="bg-primary/5 text-primary w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm">
                        {{ $grade->nilai_uts ?? $grade->nilai_tugas ?? 0 }}
                    </div>
                </div>
                @empty
                <p class="text-gray-400 text-sm italic text-center py-4">Belum ada nilai terinput.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Grid: Tonal Layering & Soft Minimalism (For Siswa) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Attendance Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm hover:shadow-md transition-shadow group">
            <div class="bg-[var(--color-surface-container-low)] w-12 h-12 rounded-2xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Presensi Hari Ini</h3>
            <p class="text-gray-500 text-sm mb-6">
                @if($attendance)
                    Waktu: {{ $attendance->created_at->format('H:i') }} WIB
                @else
                    Belum ada catatan hari ini.
                @endif
            </p>
            <div class="py-2 px-4 rounded-full text-xs font-bold inline-block {{ $attendance ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $attendance ? strtoupper($attendance->status) : 'BELUM PRESENSI' }}
            </div>
        </div>

        <!-- Schedule Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm hover:shadow-md transition-shadow group">
            <div class="bg-[var(--color-surface-container-low)] w-12 h-12 rounded-2xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Rombel Aktif</h3>
            <p class="text-gray-500 text-sm mb-6">{{ $studyGroup->nama_rombel ?? 'Belum terdaftar' }}</p>
            <div class="bg-[var(--color-surface-container-low)] py-2 px-4 rounded-full text-xs font-bold inline-block uppercase">LIHAT JADWAL</div>
        </div>

    </div>

    <!-- Extracurricular Section (Student) -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold mb-6">Ekstrakurikuler Saya</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($extracurriculars as $ekskul)
            <div class="p-5 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-primary/10 w-10 h-10 rounded-xl flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm">{{ $ekskul->nama_ekskul }}</h3>
                </div>
                <div class="flex justify-between items-center">
                    <p class="text-xs text-gray-400 line-clamp-1">Pembina: {{ $ekskul->pembina ?? 'N/A' }}</p>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shrink-0 ml-2
                        {{ $ekskul->kategori === 'wajib' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}"
                    >{{ ucwords($ekskul->kategori) }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-400 text-sm italic">Belum ada daftar ekskul.</p>
            </div>
            @endforelse
        </div>
    </div>
    @endcan

    <!-- Organic Layering Section -->
    <section class="bg-[var(--color-surface-container-low)] rounded-[3rem] p-12 overflow-hidden relative">
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-12">
            <div class="flex-1">
                <h2 class="text-4xl font-bold mb-4">E-Raport Digital</h2>
                <p class="text-gray-600 leading-relaxed mb-8">
                    Unduh dan tinjau kemajuan akademik Anda secara digital. Sederhana, aman, dan dapat diakses kapan saja.
                </p>
                <button class="bg-[#005da7] text-white px-10 py-4 rounded-full font-bold shadow-lg hover:shadow-xl hover:translate-y-[-2px] transition-all">
                    Lihat Rapor Terakhir
                </button>
            </div>
            <div class="flex-1 flex justify-center">
                <!-- Mockup/Image Placeholder with Tonal depth -->
                <div class="bg-white p-4 rounded-2xl shadow-xl rotate-3 translate-x-4">
                    <div class="w-48 h-64 bg-gray-50 rounded-lg flex flex-col p-4 gap-2">
                        <div class="h-4 w-2/3 bg-gray-200 rounded"></div>
                        <div class="h-2 w-full bg-gray-100 rounded"></div>
                        <div class="h-2 w-full bg-gray-100 rounded"></div>
                        <div class="mt-auto h-8 w-full bg-primary/20 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
