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

    $school = \Illuminate\Support\Facades\Schema::hasTable('school_settings') ? \App\Models\SchoolSetting::first() : null;
    $schoolName = $school->name ?? 'Aksara.';
    $logoUrl = $school && $school->logo ? \Illuminate\Support\Facades\Storage::url($school->logo) : asset('images/logo-nobg.png');
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

    <div class="min-h-screen md:h-screen md:overflow-hidden flex flex-col pb-20 md:pb-0 relative bg-gray-50" x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || '{{ request()->routeIs('leaves.*') ? 'attendance' : 'overview' }}' }">
        
        <!-- Desktop Top Navbar (Full Width) -->
        <header class="hidden md:flex justify-between items-center px-8 py-3 bg-white border-b border-gray-100 z-50 shrink-0">
            <div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ $logoUrl }}" alt="{{ $schoolName }}" class="h-8 w-auto object-contain drop-shadow-sm">
                    <span class="font-heading text-2xl font-bold tracking-tight text-primary">{{ $schoolName }}</span>
                </a>
            </div>
            
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
        <nav class="md:hidden bg-white px-4 py-3 flex justify-between items-center sticky top-0 z-50 shadow-sm shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <img src="{{ $logoUrl }}" alt="{{ $schoolName }}" class="h-8 w-auto object-contain drop-shadow-sm">
                <span class="font-heading text-xl font-bold tracking-tight text-primary truncate max-w-[150px]">{{ $schoolName }}</span>
            </a>
            
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

        <div class="flex-1 flex flex-col md:flex-row min-h-0">
            <!-- Desktop Sidebar -->
            <aside class="hidden md:flex flex-col w-64 shrink-0 bg-white border-r border-gray-100 h-full z-40 overflow-y-auto">
                <div class="px-4 py-6 flex-1 space-y-2">
                    <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=overview' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'overview'; $event.preventDefault(); }" :class="tab === 'overview' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span class="text-sm">Ringkasan</span>
                    </a>
                    <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=attendance' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'attendance'; $event.preventDefault(); }" :class="tab === 'attendance' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-sm">Presensi & Izin</span>
                    </a>
                    <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=academic' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'academic'; $event.preventDefault(); }" :class="tab === 'academic' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span class="text-sm">Akademik</span>
                    </a>
                    <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=activities' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'activities'; $event.preventDefault(); }" :class="tab === 'activities' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
                        <span class="text-sm">Aktivitas</span>
                    </a>
                </div>

                <div class="p-4 border-t border-gray-100 mt-auto">
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
            <div class="flex-1 flex flex-col min-w-0 md:overflow-y-auto">
                <main class="flex-1 w-full p-4 md:p-8 max-w-6xl mx-auto">
                    @yield('content')
                </main>

                <footer class="p-6 text-center text-gray-400 text-xs hidden md:block mt-auto shrink-0">
                    &copy; {{ date('Y') }} Samasta Teknologi Nuswantara. Built with Intellectual Calm.
                </footer>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-6 py-3 flex justify-between items-center z-40 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=overview' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'overview'; $event.preventDefault(); }" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'overview' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-[9px]" :class="tab === 'overview' ? 'font-bold' : 'font-medium'">Ringkasan</span>
            </a>
            
            <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=attendance' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'attendance'; $event.preventDefault(); }" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'attendance' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-[9px]" :class="tab === 'attendance' ? 'font-bold' : 'font-medium'">Kehadiran</span>
            </a>

            <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=academic' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'academic'; $event.preventDefault(); }" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'academic' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                <span class="text-[9px]" :class="tab === 'academic' ? 'font-bold' : 'font-medium'">Akademik</span>
            </a>

            <a href="{{ request()->routeIs('dashboard') ? '#' : route('dashboard') . '?tab=activities' }}" @click="if({{ request()->routeIs('dashboard') ? 'true' : 'false' }}) { tab = 'activities'; $event.preventDefault(); }" class="flex flex-col items-center gap-1 transition-colors" :class="tab === 'activities' ? 'text-primary' : 'text-gray-400 hover:text-gray-700'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
                <span class="text-[9px]" :class="tab === 'activities' ? 'font-bold' : 'font-medium'">Aktivitas</span>
            </a>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 {{ ($currentUser->student || $currentUser->parent) ? 'max-w-4xl' : 'max-w-lg' }} w-full mx-4 overflow-hidden transform scale-95 transition-all duration-300">
            <div class="px-6 py-4 bg-primary text-white flex justify-between items-center">
                <h3 class="font-heading text-lg font-bold">Profil Akun Anda</h3>
                <button id="closeProfileBtn" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            @if($currentUser->student || $currentUser->parent)
                <div class="flex flex-col lg:flex-row min-h-[400px]">
                    <!-- Left: Profile Info -->
                    <div class="w-full lg:w-80 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 flex flex-col justify-between shrink-0">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 border-b border-gray-100 pb-4">
                                <div class="w-14 h-14 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-xl border border-primary/20 shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-800 text-base leading-tight truncate">{{ $currentUser->name }}</h4>
                                    <p class="text-xs text-primary font-semibold uppercase tracking-wider mt-1">{{ $currentUser->roles->first()?->name ?? 'User' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-1 gap-4 text-xs">
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
                                        <p class="font-semibold text-primary mt-0.5 truncate">{{ $student->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                                    </div>
                                @elseif($currentUser->parent)
                                    @php $parent = $currentUser->parent; @endphp
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">No. Whatsapp</p>
                                        <p class="font-medium text-gray-700 mt-0.5">{{ $currentUser->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-span-2 lg:col-span-1">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Daftar Anak</p>
                                        <p class="font-medium text-gray-700 mt-0.5 leading-relaxed">{{ $parent->students->pluck('user.name')->implode(', ') ?: 'N/A' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="hidden lg:block pt-6 border-t border-gray-100">
                            <button id="closeProfileBtn3" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl font-bold text-xs transition-colors cursor-pointer text-center">
                                Tutup Profil
                            </button>
                        </div>
                    </div>

                    <!-- Right: Student Card(s) -->
                    <div class="flex-1 p-6 bg-gray-50/50 flex flex-col justify-between">
                        <div>
                            @if($currentUser->student)
                                @php $student = $currentUser->student; @endphp
                                <div class="flex items-center gap-2 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    <h4 class="font-heading text-sm font-bold text-gray-800">Kartu Tanda Pelajar Anda</h4>
                                </div>
                                
                                <div class="overflow-x-auto w-full flex justify-center py-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                                    <div class="flex flex-col md:flex-row gap-6 justify-center items-center min-w-max px-6">
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
                                

                            @elseif($currentUser->parent)
                                @php $parent = $currentUser->parent; @endphp
                                <div class="flex items-center gap-2 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    <h4 class="font-heading text-sm font-bold text-gray-800">Kartu Tanda Pelajar Anak Anda</h4>
                                </div>
                                
                                <div class="space-y-6 max-h-[50vh] overflow-y-auto pr-1">
                                    @forelse($parent->students as $child)
                                        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm space-y-3">
                                            <div class="flex items-center justify-between border-b border-gray-50 pb-2">
                                                <p class="text-xs font-bold text-gray-700 flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                                    {{ $child->user->name }}
                                                </p>
                                                <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-lg">NISN: {{ $child->nisn }}</span>
                                            </div>
                                            
                                            <div class="overflow-x-auto w-full flex justify-center py-2">
                                                <div class="flex flex-col md:flex-row gap-6 justify-center items-center min-w-max px-2">
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
                                        <div class="text-center py-8 text-gray-500">
                                            Belum ada data siswa yang terhubung.
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                        
                        <div class="block lg:hidden mt-6 pt-4 border-t border-gray-100 flex justify-end">
                            <button id="closeProfileBtn4" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl font-bold text-xs transition-colors cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            @else
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

                        @if($currentUser->teacher)
                            @php $teacher = $currentUser->teacher; @endphp
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">NIP</p>
                                <p class="font-medium text-gray-700 mt-0.5">{{ $teacher->nip ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status Guru</p>
                                <p class="font-medium text-gray-700 mt-0.5">{{ strtoupper($teacher->status_guru ?? 'N/A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button id="closeProfileBtn2" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-semibold text-xs transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            @endif
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
                                    default => '' . $uRole
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
            const openProfile = (e) => {
                if (e) e.preventDefault();
                profileModal.classList.remove('opacity-0', 'pointer-events-none');
                profileModal.firstElementChild.classList.remove('scale-95');
            };

            window.openProfileModal = openProfile;

            openProfileBtn.addEventListener('click', openProfile);
            
            if (openProfileBtn2) {
                openProfileBtn2.addEventListener('click', openProfile);
            }

            const closeProfile = () => {
                profileModal.classList.add('opacity-0', 'pointer-events-none');
                profileModal.firstElementChild.classList.add('scale-95');
            };
            
            if (closeProfileBtn) closeProfileBtn.addEventListener('click', closeProfile);
            if (closeProfileBtn2) closeProfileBtn2.addEventListener('click', closeProfile);
            
            const closeProfileBtn3 = document.getElementById('closeProfileBtn3');
            const closeProfileBtn4 = document.getElementById('closeProfileBtn4');
            if (closeProfileBtn3) closeProfileBtn3.addEventListener('click', closeProfile);
            if (closeProfileBtn4) closeProfileBtn4.addEventListener('click', closeProfile);

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
