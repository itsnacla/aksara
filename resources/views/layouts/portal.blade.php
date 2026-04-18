<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aksara Portal - @yield('title', 'Dashboard')</title>

    <!-- Tailwind 4 Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--color-surface)] text-[var(--color-on-surface)] selection:bg-primary/20">
    <div class="min-h-screen flex flex-col">
        <!-- Minimalist Tonal Navbar -->
        <nav class="bg-[var(--color-surface-container-low)] px-8 py-4 flex justify-between items-center sticky top-0 z-50 backdrop-blur-md bg-opacity-80">
            <div class="flex items-center gap-4">
                <span class="font-heading text-2xl font-bold tracking-tight text-primary">Aksara.</span>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-widest">{{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                    <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
                </div>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-white/50 hover:bg-white p-2 rounded-full transition-colors shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </nav>

        <main class="flex-1 max-w-7xl mx-auto w-full p-8 md:p-12">
            @yield('content')
        </main>

        <footer class="p-8 text-center text-gray-400 text-sm">
            &copy; {{ date('Y') }} Samasta Teknologi Nuswantara. Built with Intellectual Calm.
        </footer>
    </div>

    {{-- Floating AI Chatbot --}}
    @include('components.chatbot')
</body>
</html>
