@extends('layouts.portal')

@section('content')
@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
@endphp

<div class="space-y-6 md:space-y-8" id="dashboardContainer" x-cloak>
    {{-- Hero --}}
    <header class="flex justify-between items-center bg-primary rounded-3xl p-6 md:p-8 text-white shadow-sm relative overflow-hidden">
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
        
        <div class="relative z-10 flex-1">
            <p class="text-white/80 font-semibold tracking-widest uppercase text-[10px] md:text-xs mb-1">
                {{ now()->translatedFormat('l, d M Y') }}
            </p>
            <h1 class="text-2xl md:text-4xl font-bold leading-tight mb-1">
                {{ $greeting }},
            </h1>
            <p class="text-lg md:text-2xl font-medium text-white/90 truncate max-w-[200px] md:max-w-md">
                {{ auth()->user()->name }}
            </p>
            @if($academicYear)
            <div class="inline-flex items-center gap-2 mt-4 bg-white/20 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-semibold backdrop-blur-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Sem {{ $academicYear->semester }} · {{ $academicYear->tahun_ajaran }}
            </div>
            @endif
        </div>
        
        <div class="relative z-10 ml-4 hidden md:block">
            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white/50 flex items-center justify-center text-white text-xl font-bold shadow-sm">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
        </div>
    </header>

    {{-- Main Content Sections --}}

    {{-- OVERVIEW TAB --}}
    <div x-show="tab === 'overview'" x-transition.opacity class="space-y-6 md:space-y-8">
        @can('AccessParentPortal')
            @include('portal.parent.overview')
        @endcan
        @can('AccessStudentPortal')
            @include('portal.student.overview')
        @endcan
    </div>

    {{-- ATTENDANCE TAB --}}
    <div x-show="tab === 'attendance'" x-cloak x-transition.opacity class="space-y-6 md:space-y-8">
        {{-- Stat Cards (Horizontal Scroll on Mobile) --}}
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-4 -mx-4 px-4 md:mx-0 md:px-0 md:grid md:grid-cols-4 md:pb-0 hide-scrollbar" id="statsRow">
            <div class="snap-center shrink-0 w-36 md:w-auto bg-white rounded-[20px] p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-green-50 rounded-full opacity-50"></div>
                <div class="flex flex-col gap-1 relative z-10">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">Hadir</span>
                    <span class="text-3xl font-black text-gray-800" id="stat-hadir">{{ $attendanceStats['hadir'] }}</span>
                </div>
            </div>
            <div class="snap-center shrink-0 w-36 md:w-auto bg-white rounded-[20px] p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-blue-50 rounded-full opacity-50"></div>
                <div class="flex flex-col gap-1 relative z-10">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">Izin</span>
                    <span class="text-3xl font-black text-gray-800" id="stat-izin">{{ $attendanceStats['izin'] }}</span>
                </div>
            </div>
            <div class="snap-center shrink-0 w-36 md:w-auto bg-white rounded-[20px] p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-orange-50 rounded-full opacity-50"></div>
                <div class="flex flex-col gap-1 relative z-10">
                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">Sakit</span>
                    <span class="text-3xl font-black text-gray-800" id="stat-sakit">{{ $attendanceStats['sakit'] }}</span>
                </div>
            </div>
            <div class="snap-center shrink-0 w-36 md:w-auto bg-white rounded-[20px] p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-3 -top-3 w-16 h-16 bg-red-50 rounded-full opacity-50"></div>
                <div class="flex flex-col gap-1 relative z-10">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-500 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <span class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">Alpa</span>
                    <span class="text-3xl font-black text-gray-800" id="stat-alpa">{{ $attendanceStats['alpa'] }}</span>
                </div>
            </div>
        </div>

        @can('AccessParentPortal')
            @include('portal.parent.attendance')
        @endcan
        @can('AccessStudentPortal')
            @include('portal.student.attendance')
        @endcan
    </div>

    {{-- ACADEMIC TAB --}}
    <div x-show="tab === 'academic'" x-cloak x-transition.opacity class="space-y-6 md:space-y-8">
        @can('AccessParentPortal')
            @include('portal.parent.academic')
        @endcan
        @can('AccessStudentPortal')
            @include('portal.student.academic')
        @endcan
    </div>

    {{-- ACTIVITIES TAB --}}
    <div x-show="tab === 'activities'" x-cloak x-transition.opacity class="space-y-6 md:space-y-8">
        @can('AccessParentPortal')
            @include('portal.parent.activities')
        @endcan
        @can('AccessStudentPortal')
            @include('portal.student.activities')
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
