@extends('layouts.portal')

@section('content')
<div class="space-y-12">
    <!-- Hero Section: Intentional Asymmetry -->
    <header class="flex flex-col md:flex-row justify-between items-start gap-8">
        <div class="max-w-2xl">
            <span class="text-primary font-semibold tracking-widest uppercase text-xs mb-4 block">Selamat Datang di Portal Aksara</span>
            <h1 class="text-5xl md:text-7xl font-bold leading-tight mb-4">
                Selamat pagi, <br/>
                <span class="text-gray-400 italic font-medium">{{ auth()->user()->name }}</span>
            </h1>
            <p class="text-lg text-gray-500 leading-relaxed font-sans">
                Informasi sekolah Anda terorganisir di sini. Mari buat hari ini produktif dan tenang.
            </p>
        </div>
    </header>

    <!-- Grid: Tonal Layering & Soft Minimalism -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Attendance Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm hover:shadow-md transition-shadow group">
            <div class="bg-[var(--color-surface-container-low)] w-12 h-12 rounded-2xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Presensi Hari Ini</h3>
            <p class="text-gray-500 text-sm mb-6">Waktu masuk: 07:15 WIB</p>
            <div class="bg-[#e8f5e9] text-[#2e7d32] py-2 px-4 rounded-full text-xs font-bold inline-block">HADIR</div>
        </div>

        <!-- Schedule Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm hover:shadow-md transition-shadow group">
            <div class="bg-[var(--color-surface-container-low)] w-12 h-12 rounded-2xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Mata Pelajaran Berikutnya</h3>
            <p class="text-gray-500 text-sm mb-6">Matematika (10:30 - 12:00)</p>
            <div class="bg-[var(--color-surface-container-low)] py-2 px-4 rounded-full text-xs font-bold inline-block">RUANG 402</div>
        </div>

        <!-- Grade Summary -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm hover:shadow-md transition-shadow group">
            <div class="bg-[var(--color-surface-container-low)] w-12 h-12 rounded-2xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Nilai Rata-rata</h3>
            <p class="text-gray-500 text-sm mb-6">Semester Ganjil 2026</p>
            <div class="flex items-baseline gap-1">
                <span class="text-4xl font-bold text-primary">88</span>
                <span class="text-gray-400 text-sm">/ 100</span>
            </div>
        </div>
    </div>

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
