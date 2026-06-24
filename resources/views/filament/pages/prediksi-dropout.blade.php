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
                <x-filament::button type="submit" color="danger" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="analyze">Prediksi Risiko</span>
                    <span wire:loading wire:target="analyze">Sedang Menganalisis...</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>

    @if($isAnalyzing)
        <x-filament::card class="mt-6 text-center py-12">
            <x-filament::loading-indicator class="h-10 w-10 text-danger-500 mx-auto mb-4" />
            <h3 class="text-lg font-bold">AI sedang mengevaluasi...</h3>
            <p class="text-gray-500">Mengkalkulasi probabilitas dropout berdasarkan riwayat nilai dan pola presensi siswa.</p>
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
            @php
                $level = strtolower($aiResult['risk_level'] ?? '');
                if (str_contains($level, 'rendah')) {
                    $riskColorStyle = 'background-color: #10b981;'; // success
                } elseif (str_contains($level, 'menengah') || str_contains($level, 'sedang')) {
                    $riskColorStyle = 'background-color: #f59e0b;'; // warning
                } elseif (str_contains($level, 'tinggi')) {
                    $riskColorStyle = 'background-color: #ef4444;'; // danger
                } else {
                    $riskColorStyle = 'background-color: #6b7280;'; // default
                }
                $score = (int) ($aiResult['risk_score'] ?? 0);
            @endphp
            <div class="mt-8 space-y-6">
                
                <!-- Main Status Banner -->
                <div class="rounded-xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between" style="{{ $riskColorStyle }}">
                    <div>
                        <p class="text-white/90 font-medium mb-1">Hasil Prediksi Risiko untuk {{ $aiResult['student_name'] ?? 'Siswa' }}</p>
                        <h2 class="text-4xl font-extrabold uppercase tracking-wide">TINGKAT {{ $aiResult['risk_level'] ?? 'N/A' }}</h2>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <p class="text-white/90 text-sm mb-1 font-semibold">Risk Score</p>
                        <div class="text-5xl font-black">{{ $score }}<span class="text-2xl text-white/80">%</span></div>
                    </div>
                </div>

                <!-- Analysis Text -->
                <x-filament::card>
                    <div class="flex items-start">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg mr-4 mt-1">
                            <x-filament::icon icon="heroicon-o-magnifying-glass-circle" class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-2">Analisis AI</h3>
                            <p class="text-gray-700 dark:text-gray-300">{{ $aiResult['analysis'] ?? '-' }}</p>
                        </div>
                    </div>
                </x-filament::card>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Warning Flags -->
                    <x-filament::card class="border-t-4 border-t-danger-500">
                        <div class="flex items-center text-danger-600 mb-4">
                            <x-filament::icon icon="heroicon-o-flag" class="w-6 h-6 mr-2" />
                            <h3 class="font-bold text-lg">Indikator Bahaya (Red Flags)</h3>
                        </div>
                        <ul class="space-y-3">
                            @if(!empty($aiResult['warning_flags']) && is_array($aiResult['warning_flags']))
                                @foreach($aiResult['warning_flags'] as $flag)
                                    <li class="flex items-start">
                                        <x-filament::icon icon="heroicon-m-x-circle" class="w-5 h-5 text-danger-500 mr-2 shrink-0 mt-0.5" />
                                        <span class="text-gray-700 dark:text-gray-300">{{ $flag }}</span>
                                    </li>
                                @endforeach
                            @else
                                <p class="text-gray-500 italic">Tidak terdeteksi indikator bahaya yang signifikan.</p>
                            @endif
                        </ul>
                    </x-filament::card>

                    <!-- Preventive Actions -->
                    <x-filament::card class="border-t-4 border-t-success-500">
                        <div class="flex items-center text-success-600 mb-4">
                            <x-filament::icon icon="heroicon-o-shield-check" class="w-6 h-6 mr-2" />
                            <h3 class="font-bold text-lg">Tindakan Pencegahan</h3>
                        </div>
                        <ul class="space-y-3">
                            @if(!empty($aiResult['preventive_actions']) && is_array($aiResult['preventive_actions']))
                                @foreach($aiResult['preventive_actions'] as $action)
                                    <li class="flex items-start">
                                        <x-filament::icon icon="heroicon-m-check-circle" class="w-5 h-5 text-success-500 mr-2 shrink-0 mt-0.5" />
                                        <span class="text-gray-700 dark:text-gray-300">{{ $action }}</span>
                                    </li>
                                @endforeach
                            @else
                                <p class="text-gray-500 italic">Tidak ada rekomendasi spesifik.</p>
                            @endif
                        </ul>
                    </x-filament::card>
                </div>

            </div>
        @endif

    @endif

</x-filament-panels::page>
