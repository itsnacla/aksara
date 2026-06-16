@props(['subtitle' => 'Silakan masuk ke akun Anda'])

<div class="flex min-h-screen bg-gray-50">
    <!-- Left Side: Visual / Illustration -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-blue-900 items-center justify-center">
        <!-- Background Image with Overlay -->
        <img src="{{ asset('images/background.png') }}" 
             class="absolute inset-0 w-full h-full object-cover z-0" 
             alt="School Background">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/90 to-blue-600/80 z-10 mix-blend-multiply"></div>
        
        <div class="relative z-20 text-white p-12 max-w-2xl text-center">
            <div class="inline-block p-4 rounded-2xl bg-white/10 backdrop-blur-md mb-8 ring-1 ring-white/20 shadow-2xl">
                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6 tracking-tight text-white drop-shadow-md">
                Selamat Datang di<br>Portal Akademik
            </h1>
            <p class="text-lg md:text-xl text-blue-50 font-medium drop-shadow-sm max-w-lg mx-auto leading-relaxed">
                Wujudkan Generasi Berprestasi Bersama Kami. Akses sistem informasi akademik terpadu dengan mudah dan aman.
            </p>
            
            <div class="mt-12 flex items-center justify-center space-x-2 text-sm text-blue-200">
                <span>Sistem Informasi Terintegrasi</span>
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                <span>Tahun Ajaran Aktif</span>
            </div>
        </div>
    </div>

    <!-- Right Side: Content -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-white/80 backdrop-blur-xl">
        <div class="w-full max-w-md">
            @php
                $school = \App\Models\SchoolSetting::first();
                $logoUrl = $school && $school->logo ? \Illuminate\Support\Facades\Storage::url($school->logo) : asset('images/logo-nobg.png');
                $schoolName = $school && $school->name ? $school->name : 'AKSARA';
                $schoolEmail = $school && $school->email ? $school->email : 'support@samastanuswantara.com';
            @endphp

            <div class="text-center mb-10">
                <img src="{{ $logoUrl }}" alt="Logo {{ $schoolName }}" class="h-24 mx-auto mb-6 object-contain drop-shadow-sm hover:scale-105 transition-transform duration-300">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $schoolName }}</h2>
                <p class="text-gray-500 mt-2 font-medium">{{ $subtitle }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-8">
                {{ $slot }}
            </div>

            <div class="mt-10 text-center text-sm font-medium text-gray-500">
                Mengalami kendala? <a href="mailto:{{ $schoolEmail }}" class="text-blue-600 hover:text-blue-800 transition-colors border-b border-transparent hover:border-blue-800 pb-0.5">Hubungi Admin</a>
            </div>
            
            <div class="mt-8 text-center text-xs text-gray-400">
                <p>&copy; {{ date('Y') }} {{ $schoolName }}. Hak Cipta Dilindungi.</p>
                <p class="mt-1">Developed & Maintained by <a href="https://samastanuswantara.com" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors font-medium">Tateta</a></p>
            </div>
        </div>
    </div>
</div>
