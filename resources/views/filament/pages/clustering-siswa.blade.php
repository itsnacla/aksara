<x-filament-panels::page>

    <div class="mb-4">
        <a href="{{ \App\Filament\Pages\PusatAnalisisData::getUrl() }}" class="text-sm font-medium text-gray-500 hover:text-primary-600 flex items-center">
            <x-filament::icon icon="heroicon-m-arrow-left" class="w-4 h-4 mr-1" />
            Kembali ke Pusat Analisis
        </a>
    </div>

    <x-filament::card>
        <form wire:submit="analyze" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="analyze">Mulai Analisis</span>
                    <span wire:loading wire:target="analyze">Sedang Menganalisis...</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>

    @if($isAnalyzing)
        <x-filament::card class="mt-6 text-center py-12">
            <x-filament::loading-indicator class="h-10 w-10 text-primary-500 mx-auto mb-4" />
            <h3 class="text-lg font-bold">AI sedang berpikir...</h3>
            <p class="text-gray-500">Menganalisis pola puluhan nilai siswa untuk menemukan gaya belajar mereka. Mohon tunggu sebentar.</p>
        </x-filament::card>
    @elseif($aiResult)
        
        @if(isset($aiResult['error']))
            <x-filament::card class="mt-6 border-danger-500 ring-1 ring-danger-500">
                <div class="flex items-center text-danger-600 mb-2">
                    <x-filament::icon icon="heroicon-o-exclamation-circle" class="w-6 h-6 mr-2" />
                    <h3 class="font-bold text-lg">Analisis Gagal</h3>
                </div>
                <p>{{ $aiResult['error'] }}</p>
            </x-filament::card>
        @else
            <div class="mt-8 space-y-6">
                <!-- Header Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament::card>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Kelas Dianalisis</p>
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $aiResult['class_name'] ?? 'N/A' }}</p>
                    </x-filament::card>
                    <x-filament::card>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Siswa Terdeteksi</p>
                        <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $aiResult['total_students'] ?? 'N/A' }} Siswa</p>
                    </x-filament::card>
                </div>

                <!-- Insights -->
                <x-filament::card class="border-l-4 border-l-primary-500">
                    <div class="flex items-center mb-3 text-primary-600 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-light-bulb" class="w-6 h-6 mr-2" />
                        <h3 class="font-bold text-lg">Kesimpulan Umum</h3>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $aiResult['insights'] ?? '-' }}</p>
                </x-filament::card>

                <!-- Clusters Grid -->
                <div>
                    <h3 class="font-bold text-xl mb-4">Pengelompokan Gaya Belajar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @if(!empty($aiResult['clusters']) && is_array($aiResult['clusters']))
                            @foreach($aiResult['clusters'] as $index => $cluster)
                                @php
                                    $name = strtolower($cluster['name'] ?? '');
                                    
                                    if (str_contains($name, 'tinggi') || str_contains($name, 'seimbang') || str_contains($name, 'prestasi')) {
                                        // Indigo / Violet (Berprestasi / Seimbang)
                                        $themeClass = 'bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-800 dark:text-indigo-300 border-indigo-100 dark:border-indigo-900/50';
                                        $badgeClass = 'bg-indigo-100/80 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300';
                                        $icon = 'academic-cap';
                                    } elseif (str_contains($name, 'logika') || str_contains($name, 'matematika') || str_contains($name, 'numer') || str_contains($name, 'eksakta')) {
                                        // Blue / Cyan (Logika-Matematika)
                                        $themeClass = 'bg-blue-50/60 dark:bg-blue-950/30 text-blue-800 dark:text-blue-300 border-blue-100 dark:border-blue-900/50';
                                        $badgeClass = 'bg-blue-100/80 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300';
                                        $icon = 'calculator';
                                    } elseif (str_contains($name, 'bahasa') || str_contains($name, 'seni') || str_contains($name, 'humaniora') || str_contains($name, 'komunikasi')) {
                                        // Emerald / Teal (Bahasa / Seni / Humaniora)
                                        $themeClass = 'bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-300 border-emerald-100 dark:border-emerald-900/50';
                                        $badgeClass = 'bg-emerald-100/80 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300';
                                        $icon = 'pencil-square';
                                    } elseif (str_contains($name, 'dukungan') || str_contains($name, 'intensif') || str_contains($name, 'bimbingan') || str_contains($name, 'perlu')) {
                                        // Rose / Amber (Memerlukan Dukungan)
                                        $themeClass = 'bg-rose-50/60 dark:bg-rose-950/30 text-rose-800 dark:text-rose-300 border-rose-100 dark:border-rose-900/50';
                                        $badgeClass = 'bg-rose-100/80 dark:bg-rose-900/50 text-rose-800 dark:text-rose-300';
                                        $icon = 'exclamation-triangle';
                                    } else {
                                        // Fallback based on index modulo
                                        $modulo = $index % 4;
                                        if ($modulo === 0) {
                                            $themeClass = 'bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-800 dark:text-indigo-300 border-indigo-100 dark:border-indigo-900/50';
                                            $badgeClass = 'bg-indigo-100/80 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300';
                                            $icon = 'academic-cap';
                                        } elseif ($modulo === 1) {
                                            $themeClass = 'bg-blue-50/60 dark:bg-blue-950/30 text-blue-800 dark:text-blue-300 border-blue-100 dark:border-blue-900/50';
                                            $badgeClass = 'bg-blue-100/80 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300';
                                            $icon = 'calculator';
                                        } elseif ($modulo === 2) {
                                            $themeClass = 'bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-300 border-emerald-100 dark:border-emerald-900/50';
                                            $badgeClass = 'bg-emerald-100/80 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300';
                                            $icon = 'pencil-square';
                                        } else {
                                            $themeClass = 'bg-rose-50/60 dark:bg-rose-950/30 text-rose-800 dark:text-rose-300 border-rose-100 dark:border-rose-900/50';
                                            $badgeClass = 'bg-rose-100/80 dark:bg-rose-900/50 text-rose-800 dark:text-rose-300';
                                            $icon = 'exclamation-triangle';
                                        }
                                    }
                                @endphp
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-xs flex flex-col h-full bg-white dark:bg-gray-900/60 backdrop-blur-md transition-all duration-300 hover:shadow-md hover:scale-[1.01]">
                                    <div class="p-5 {{ $themeClass }} border-b border-gray-100 dark:border-gray-800/80">
                                        <div class="flex justify-between items-start">
                                            <div class="flex items-center space-x-3">
                                                <div class="p-2 rounded-lg bg-white/70 dark:bg-black/30 shadow-xs">
                                                    <x-filament::icon icon="heroicon-o-{{ $icon }}" class="w-5 h-5" />
                                                </div>
                                                <h4 class="font-bold text-base leading-tight">{{ $cluster['name'] ?? 'Kategori' }}</h4>
                                            </div>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }} shadow-xs">
                                                {{ $cluster['percentage'] ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-5 flex-grow">
                                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Daftar Siswa:</p>
                                        <div class="flex flex-wrap gap-1.5 mb-4">
                                            @if(isset($cluster['students']) && is_array($cluster['students']))
                                                @foreach($cluster['students'] as $student)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200/50 dark:border-gray-700/50">
                                                        {{ $student }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-sm text-gray-400 italic">Tidak ada siswa</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-5 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-800/40 mt-auto">
                                        <div class="flex items-center space-x-1.5 mb-2 text-gray-500 dark:text-gray-400">
                                            <x-filament::icon icon="heroicon-m-chat-bubble-bottom-center-text" class="w-4 h-4" />
                                            <p class="text-xs font-bold uppercase tracking-wider">Rekomendasi Guru</p>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-normal">{{ $cluster['recommendation'] ?? '-' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endif

    @endif

</x-filament-panels::page>
