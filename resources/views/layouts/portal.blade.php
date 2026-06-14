<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aksara Portal - @yield('title', 'Dashboard')</title>

    <!-- Tailwind 4 Styles & App Scripts -->
    @vite(['resources/css/app.css', 'resources/css/chatbot.css', 'resources/js/app.js', 'resources/js/chatbot.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $currentUser = auth()->user();
    $roleName = strtolower($currentUser->roles->first()?->name ?? '');
    $isSuperAdmin = str_contains($roleName, 'super_admin') || str_contains($roleName, 'admin');
    
    $impersonateUsers = [];
    if ($isSuperAdmin) {
        $impersonateUsers = \App\Models\User::where('id', '!=', $currentUser->id)->with('roles')->orderBy('name', 'asc')->get();
    }

    $initials = strtoupper(substr($currentUser->name, 0, 2));
@endphp
<body class="bg-[var(--color-surface)] text-[var(--color-on-surface)] selection:bg-primary/20">
    <!-- Impersonation Warning Banner -->
    @if(session()->has('impersonator_id'))
        <div class="bg-amber-500 text-white px-8 py-3 flex justify-between items-center text-sm font-medium z-[100] relative shadow-md">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>Anda sedang login sebagai <strong>{{ $currentUser->name }}</strong> (Mode Impersonasi).</span>
            </div>
            <form action="{{ route('impersonate.logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white text-amber-600 hover:bg-amber-50 px-4 py-1.5 rounded-lg font-semibold text-xs transition-colors shadow-sm cursor-pointer">
                    Kembali ke Admin
                </button>
            </form>
        </div>
    @endif

    <div class="min-h-screen flex flex-col md:flex-row pb-20 md:pb-0 relative bg-gray-50" x-data="{ tab: 'overview' }">
        
        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-gray-100 h-screen sticky top-0 z-40">
            <div class="px-6 py-8">
                <a href="{{ route('dashboard') }}" class="font-heading text-2xl font-bold tracking-tight text-primary">Aksara.</a>
            </div>
            
            <div class="px-4 flex-1 space-y-2">
                <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span class="text-sm">Ringkasan</span>
                </button>
                <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm">Presensi & Izin</span>
                </button>
                <button @click="tab = 'academic'" :class="tab === 'academic' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    <span class="text-sm">Akademik</span>
                </button>
                <button @click="tab = 'activities'" :class="tab === 'activities' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
                    <span class="text-sm">Aktivitas</span>
                </button>
            </div>

            <div class="p-4 border-t border-gray-100">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 w-full text-left rounded-xl text-red-500 hover:bg-red-50 transition-colors font-medium cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span class="text-sm">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Desktop Top Navbar -->
            <header class="hidden md:flex justify-end items-center px-8 py-3 bg-white border-b border-gray-100 z-30 sticky top-0">
                <div class="relative group">
                    <button class="flex items-center gap-3 hover:bg-gray-50 px-3 py-1.5 rounded-xl transition-colors cursor-pointer">
                        <div class="text-right">
                            <p class="font-bold text-sm text-gray-800">{{ $currentUser->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ $currentUser->roles->first()?->name ?? 'User' }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-sm border border-primary/20 shrink-0">
                            {{ $initials }}
                        </div>
                    </button>
                    <div class="absolute right-0 top-full mt-1 w-56 bg-white rounded-2xl shadow-sm border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[60]">
                        <a href="#" id="openProfileBtn2" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-primary transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Profil Saya
                        </a>
                        @if($isSuperAdmin)
                        <a href="#" id="openImpersonateBtn2" class="flex items-center gap-3 px-4 py-2 text-sm text-amber-600 hover:bg-amber-50 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Login As (Impersonate)
                        </a>
                        @endif
                        <div class="border-t border-gray-50 my-2"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors cursor-pointer text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Mobile Top Navbar -->
            <nav class="md:hidden bg-white px-4 py-3 flex justify-between items-center sticky top-0 z-30 shadow-sm">
                <a href="{{ route('dashboard') }}" class="font-heading text-xl font-bold tracking-tight text-primary">Aksara.</a>
                
                <!-- Mobile User Dropdown -->
                <button id="userDropdownBtn" class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-sm border border-primary/20">
                    {{ $initials }}
                </button>

                <!-- Dropdown Menu -->
                <div id="userDropdownMenu" class="absolute right-4 top-14 w-56 bg-white rounded-2xl shadow-sm border border-gray-100 py-2 opacity-0 pointer-events-none transform scale-95 origin-top-right transition-all duration-200 z-[60]">
                    <div class="px-4 py-2.5 border-b border-gray-50">
                        <p class="font-semibold text-sm text-gray-800 truncate">{{ $currentUser->name }}</p>
                        <p class="text-[10px] text-gray-500 truncate mt-0.5">{{ $currentUser->roles->first()?->name ?? 'User' }}</p>
                    </div>

                    <a href="#" id="openProfileBtn" class="flex items-center gap-3 px-4 py-2.5 text-xs text-gray-600 hover:bg-gray-50 hover:text-primary transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Profil Saya</span>
                    </a>

                    @if($isSuperAdmin)
                        <a href="#" id="openImpersonateBtn" class="flex items-center gap-3 px-4 py-2.5 text-xs text-gray-600 hover:bg-gray-50 hover:text-primary transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span class="font-medium text-amber-600">Login As</span>
                        </a>
                    @endif
                    <div class="border-t border-gray-50 my-2"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs text-red-500 hover:bg-red-50 transition-colors cursor-pointer text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </nav>

            <main class="flex-1 w-full p-4 md:p-8 max-w-6xl mx-auto">
                @yield('content')
            </main>

            <footer class="p-6 text-center text-gray-400 text-xs hidden md:block mt-auto">
                &copy; {{ date('Y') }} Samasta Teknologi Nuswantara. Built with Intellectual Calm.
            </footer>
        </div>

        <!-- Mobile Bottom Navigation -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-6 py-3 flex justify-between items-center z-40 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <button @click="tab = 'overview'" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'overview' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-[9px]" :class="tab === 'overview' ? 'font-bold' : 'font-medium'">Ringkasan</span>
            </button>
            
            <button @click="tab = 'attendance'" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'attendance' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-[9px]" :class="tab === 'attendance' ? 'font-bold' : 'font-medium'">Kehadiran</span>
            </button>

            <button @click="tab = 'academic'" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'academic' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                <span class="text-[9px]" :class="tab === 'academic' ? 'font-bold' : 'font-medium'">Akademik</span>
            </button>

            <button @click="tab = 'activities'" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'activities' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
                <span class="text-[9px]" :class="tab === 'activities' ? 'font-bold' : 'font-medium'">Aktivitas</span>
            </button>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-lg w-full mx-4 overflow-hidden transform scale-95 transition-all duration-300">
            <div class="px-6 py-4 bg-primary text-white flex justify-between items-center">
                <h3 class="font-heading text-lg font-bold">Profil Akun Anda</h3>
                <button id="closeProfileBtn" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4 border-b border-gray-100 pb-4">
                    <div class="w-14 h-14 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-xl border border-primary/20">
                        {{ $initials }}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base leading-tight">{{ $currentUser->name }}</h4>
                        <p class="text-xs text-primary font-semibold uppercase tracking-wider mt-1">{{ $currentUser->roles->first()?->name ?? 'User' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alamat Email</p>
                        <p class="font-medium text-gray-700 mt-0.5 truncate">{{ $currentUser->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status Akun</p>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                            Aktif
                        </span>
                    </div>

                    @if($currentUser->student)
                        @php $student = $currentUser->student; @endphp
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
                            <p class="font-semibold text-primary mt-0.5">{{ $student->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                        </div>
                    @elseif($currentUser->teacher)
                        @php $teacher = $currentUser->teacher; @endphp
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">NIP</p>
                            <p class="font-medium text-gray-700 mt-0.5">{{ $teacher->nip ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status Guru</p>
                            <p class="font-medium text-gray-700 mt-0.5">{{ strtoupper($teacher->status_guru ?? 'N/A') }}</p>
                        </div>
                    @elseif($currentUser->parent)
                        @php $parent = $currentUser->parent; @endphp
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">No. Whatsapp</p>
                            <p class="font-medium text-gray-700 mt-0.5">{{ $currentUser->phone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Daftar Anak</p>
                            <p class="font-medium text-gray-700 mt-0.5">{{ $parent->students->pluck('user.name')->implode(', ') ?: 'N/A' }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button id="closeProfileBtn2" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-semibold text-xs transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Impersonate Modal (Login As) -->
    @if($isSuperAdmin)
        <div id="impersonateModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-xl w-full mx-4 overflow-hidden transform scale-95 transition-all duration-300">
                <div class="px-6 py-4 bg-amber-500 text-white flex justify-between items-center">
                    <h3 class="font-heading text-lg font-bold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                        </svg>
                        <span>Login As (Impersonasi User)</span>
                    </h3>
                    <button id="closeImpersonateBtn" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="relative mb-4">
                        <input type="text" id="userSearchInput" placeholder="Cari berdasarkan nama, email, atau role..." class="w-full px-4 py-2.5 pl-10 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-3.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 border border-gray-100 rounded-xl" id="userListContainer">
                        @foreach($impersonateUsers as $u)
                            @php 
                                $uRole = $u->roles->first()?->name ?? 'User';
                                $uRoleDisplay = match(strtolower($uRole)) {
                                    'siswa' => '🎓 Siswa',
                                    'orang_tua', 'wali' => '👥 Orang Tua',
                                    'guru', 'teacher' => '👨‍🏫 Guru',
                                    'staff' => '⚙️ Staff',
                                    default => '👤 ' . $uRole
                                };
                            @endphp
                            <div class="user-item flex justify-between items-center px-4 py-3 hover:bg-gray-50 transition-colors" data-search="{{ strtolower($u->name) }} {{ strtolower($u->email) }} {{ strtolower($uRole) }}">
                                <div class="text-left">
                                    <p class="font-semibold text-xs text-gray-800 leading-tight">{{ $u->name }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5 leading-none">{{ $u->email }}</p>
                                    <span class="inline-block text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded mt-1.5">{{ $uRoleDisplay }}</span>
                                </div>
                                <form action="{{ route('impersonate.login', $u) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg font-bold text-[10px] transition-colors shadow-sm cursor-pointer">
                                        Login As
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Floating AI Chatbot --}}
    @include('components.chatbot')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userDropdownBtn = document.getElementById('userDropdownBtn');
            const userDropdownMenu = document.getElementById('userDropdownMenu');
            const openProfileBtn = document.getElementById('openProfileBtn');
            const profileModal = document.getElementById('profileModal');
            const closeProfileBtn = document.getElementById('closeProfileBtn');
            const closeProfileBtn2 = document.getElementById('closeProfileBtn2');
            const openProfileBtn2 = document.getElementById('openProfileBtn2');

            // Toggle Dropdown
            userDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('opacity-0');
                userDropdownMenu.classList.toggle('pointer-events-none');
                userDropdownMenu.classList.toggle('scale-95');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', () => {
                userDropdownMenu.classList.add('opacity-0');
                userDropdownMenu.classList.add('pointer-events-none');
                userDropdownMenu.classList.add('scale-95');
            });

            // Handle Profile Modal
            openProfileBtn.addEventListener('click', (e) => {
                e.preventDefault();
                profileModal.classList.remove('opacity-0', 'pointer-events-none');
                profileModal.firstElementChild.classList.remove('scale-95');
            });
            
            if (openProfileBtn2) {
                openProfileBtn2.addEventListener('click', (e) => {
                    e.preventDefault();
                    profileModal.classList.remove('opacity-0', 'pointer-events-none');
                    profileModal.firstElementChild.classList.remove('scale-95');
                });
            }

            const closeProfile = () => {
                profileModal.classList.add('opacity-0', 'pointer-events-none');
                profileModal.firstElementChild.classList.add('scale-95');
            };
            closeProfileBtn.addEventListener('click', closeProfile);
            closeProfileBtn2.addEventListener('click', closeProfile);

            // Handle Impersonate Modal
            @if($isSuperAdmin)
                const openImpersonateBtn = document.getElementById('openImpersonateBtn');
                const impersonateModal = document.getElementById('impersonateModal');
                const closeImpersonateBtn = document.getElementById('closeImpersonateBtn');
                const userSearchInput = document.getElementById('userSearchInput');
                const userItems = document.querySelectorAll('.user-item');

                openImpersonateBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    impersonateModal.classList.remove('opacity-0', 'pointer-events-none');
                    impersonateModal.firstElementChild.classList.remove('scale-95');
                });

                closeImpersonateBtn.addEventListener('click', () => {
                    impersonateModal.classList.add('opacity-0', 'pointer-events-none');
                    impersonateModal.firstElementChild.classList.add('scale-95');
                });

                // Search filtering
                userSearchInput.addEventListener('input', (e) => {
                    const term = e.target.value.toLowerCase();
                    userItems.forEach(item => {
                        const searchData = item.getAttribute('data-search');
                        if (searchData.includes(term)) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });
                });

                // Auto trigger if URL has trigger_impersonate parameter
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('trigger_impersonate')) {
                    impersonateModal.classList.remove('opacity-0', 'pointer-events-none');
                    impersonateModal.firstElementChild.classList.remove('scale-95');
                    userSearchInput.focus();
                }
            @endif
        });
    </script>
    @livewireScripts
</body>
</html>
