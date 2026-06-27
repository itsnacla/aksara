@extends('layouts.portal')

@section('content')
@php
    $currentUser = auth()->user();
    $initials = strtoupper(substr($currentUser->name, 0, 2));

    $phone = null;
    if ($currentUser->student) {
        $phone = $currentUser->student->phone;
    } elseif ($currentUser->parent) {
        $phone = $currentUser->parent->no_whatsapp;
    } elseif ($currentUser->teacher) {
        $phone = $currentUser->teacher->no_whatsapp;
    }
@endphp

<div class="space-y-6 md:space-y-8">
    {{-- Page Header --}}
    <header class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary font-bold flex items-center justify-center text-xl shrink-0">
                {{ $initials }}
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">{{ $currentUser->name }}</h1>
                <p class="text-xs font-semibold text-primary/80 tracking-wider uppercase mt-1">Profil & Kartu Tanda Pelajar</p>
            </div>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl text-xs font-bold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </header>

    {{-- Main Profile Container - Stacked Top-to-Bottom --}}
    <div class="space-y-6 md:space-y-8">
        <!-- Account Details Card -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Column 1: Account Info -->
                <div>
                    <h3 class="text-sm font-bold text-gray-800 border-b border-gray-50 pb-3 mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Informasi Akun
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alamat Email</p>
                            <p class="font-medium text-gray-700 mt-0.5 truncate">{{ $currentUser->email }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status Akun</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 mt-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                                Aktif
                            </span>
                        </div>
                        @if($phone)
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">No. Whatsapp</p>
                            <p class="font-medium text-gray-700 mt-0.5">{{ $phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Column 2 & 3: Role Specific Details -->
                @if($currentUser->student)
                    @php $student = $currentUser->student; @endphp
                    <div class="md:col-span-1 lg:col-span-2">
                        <h3 class="text-sm font-bold text-gray-800 border-b border-gray-50 pb-3 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            Detail Siswa
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">NISN</p>
                                <p class="font-medium text-gray-700 mt-0.5">{{ $student->nisn ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">NIS</p>
                                <p class="font-medium text-gray-700 mt-0.5">{{ $student->nis ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tempat, Tanggal Lahir</p>
                                <p class="font-medium text-gray-700 mt-0.5">{{ $student->pob ?? '-' }}, {{ $student->dob ? $student->dob->format('d M Y') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rombel Aktif</p>
                                <p class="font-semibold text-primary mt-0.5 truncate">{{ $student->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                            </div>
                        </div>
                    </div>
                @elseif($currentUser->parent)
                    @php $parent = $currentUser->parent; @endphp
                    <div class="md:col-span-1 lg:col-span-2">
                        <h3 class="text-sm font-bold text-gray-800 border-b border-gray-50 pb-3 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            Detail Wali Murid
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Daftar Anak</p>
                                <p class="font-medium text-gray-700 mt-0.5 leading-relaxed">{{ $parent->students->pluck('user.name')->implode(', ') ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Student Card(s) Card -->
        <div class="space-y-6 md:space-y-8">
            @if($currentUser->student)
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-50 pb-4">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            <h4 class="font-heading text-base font-bold text-gray-800">Kartu Tanda Pelajar Anda</h4>
                        </div>
                        <div>
                            <a href="{{ route('student.card', $student->id) }}" target="_blank" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/95 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak Kartu Pelajar
                            </a>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto w-full flex justify-center py-6 bg-gray-50/50 rounded-2xl border border-gray-100">
                        <div class="flex flex-col md:flex-row gap-8 justify-center items-center min-w-max px-6">
                            <div class="relative group">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-emerald-500 rounded-[14px] blur opacity-15 group-hover:opacity-30 transition duration-300"></div>
                                <div class="relative bg-white rounded-[13px] p-0.5 shadow-sm">
                                    <x-student-card :student="$student" :school="$school" side="front" />
                                </div>
                            </div>
                            <div class="relative group">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-emerald-500 rounded-[14px] blur opacity-15 group-hover:opacity-30 transition duration-300"></div>
                                <div class="relative bg-white rounded-[13px] p-0.5 shadow-sm">
                                    <x-student-card :student="$student" :school="$school" side="back" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($currentUser->parent)
                @forelse($children as $child)
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-50 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($child->user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-gray-800 truncate">{{ $child->user->name }}</h4>
                                    <p class="text-[10px] text-gray-400 font-medium">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2.5 py-1.5 rounded-lg">NISN: {{ $child->nisn }}</span>
                                <a href="{{ route('student.card', $child->id) }}" target="_blank" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/95 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Cetak Kartu
                                </a>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto w-full flex justify-center py-6 bg-gray-50/50 rounded-2xl border border-gray-100">
                            <div class="flex flex-col md:flex-row gap-8 justify-center items-center min-w-max px-6">
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-emerald-500 rounded-[14px] blur opacity-15 group-hover:opacity-30 transition duration-300"></div>
                                    <div class="relative bg-white rounded-[13px] p-0.5 shadow-sm">
                                        <x-student-card :student="$child" :school="$school" side="front" />
                                    </div>
                                </div>
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-emerald-500 rounded-[14px] blur opacity-15 group-hover:opacity-30 transition duration-300"></div>
                                    <div class="relative bg-white rounded-[13px] p-0.5 shadow-sm">
                                        <x-student-card :student="$child" :school="$school" side="back" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] text-center text-gray-500">
                        Belum ada data siswa yang terhubung.
                    </div>
                @endforelse
            @endif
        </div>
    </div>
</div>
@endsection
