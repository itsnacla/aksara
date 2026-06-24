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
                        <p class="text-sm text-gray-500 font-medium">Kelas Dianalisis</p>
                        <p class="text-3xl font-bold text-primary-600">{{ $aiResult['class_name'] ?? 'N/A' }}</p>
                    </x-filament::card>
                    <x-filament::card>
                        <p class="text-sm text-gray-500 font-medium">Total Siswa Terdeteksi</p>
                        <p class="text-3xl font-bold text-success-600">{{ $aiResult['total_students'] ?? 'N/A' }} Siswa</p>
                    </x-filament::card>
                </div>

                <!-- Insights -->
                <x-filament::card>
                    <div class="flex items-center mb-2 text-primary-600">
                        <x-filament::icon icon="heroicon-o-light-bulb" class="w-6 h-6 mr-2" />
                        <h3 class="font-bold text-lg">Kesimpulan Umum</h3>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">{{ $aiResult['insights'] ?? '-' }}</p>
                </x-filament::card>

                <!-- Clusters Grid -->
                <div>
                    <h3 class="font-bold text-xl mb-4">Pengelompokan Gaya Belajar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @if(!empty($aiResult['clusters']) && is_array($aiResult['clusters']))
                            @foreach($aiResult['clusters'] as $cluster)
                                @php
                                    $colorClass = $cluster['color'] ?? 'bg-gray-100';
                                @endphp
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm flex flex-col h-full bg-white dark:bg-gray-900">
                                    <div class="p-4 {{ str_contains($colorClass, 'bg-') ? $colorClass : 'bg-primary-50 dark:bg-primary-900/30' }} border-b border-gray-100 dark:border-gray-800">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-bold text-lg">{{ $cluster['name'] ?? 'Cluster' }}</h4>
                                            <span class="inline-flex items-center rounded-md bg-white/60 dark:bg-black/40 px-2 py-1 text-xs font-medium text-gray-800 dark:text-gray-200 ring-1 ring-inset ring-gray-500/10">{{ $cluster['percentage'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="p-4 flex-grow">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 font-medium">Daftar Siswa:</p>
                                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1 mb-4">
                                            @if(isset($cluster['students']) && is_array($cluster['students']))
                                                @foreach($cluster['students'] as $student)
                                                    <li>{{ $student }}</li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 mt-auto">
                                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Rekomendasi Guru</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $cluster['recommendation'] ?? '-' }}</p>
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
