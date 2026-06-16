<div class="flex flex-col gap-4">
    @if(count($tanggungan) > 0)
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Berikut adalah rincian tugas yang belum selesai untuk rombel ini:
        </p>
        
        <div class="flex flex-col gap-3">
            @foreach($tanggungan as $item)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-white/5 dark:border-white/10 gap-4">
                    <div class="space-y-1">
                        <h4 class="font-semibold text-gray-900 dark:text-white">
                            {{ $item['title'] }}
                        </h4>
                        <div class="flex items-center gap-2 text-sm font-medium text-warning-600 dark:text-warning-400">
                            <x-heroicon-m-exclamation-triangle class="w-4 h-4" />
                            <span>{{ $item['text'] }}</span>
                        </div>
                        @if(!empty($item['missing']))
                            <div class="mt-3 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                                <p class="text-sm font-bold text-danger-700 dark:text-danger-400 mb-1">Daftar Siswa yang Belum Selesai:</p>
                                <p class="text-sm text-danger-600 dark:text-danger-300 leading-relaxed">
                                    {{ str_replace('Belum dinilai: ', '', str_replace('Belum dicetak: ', '', str_replace('Belum diabsen: ', '', str_replace('Belum ada catatan: ', '', str_replace('Belum dipublikasi: ', '', str_replace('Belum dinilai lengkap: ', '', $item['missing'])))))) }}
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    <x-filament::button
                        tag="a"
                        href="{{ $item['url'] }}"
                        size="sm"
                        icon="heroicon-m-arrow-right"
                        icon-position="after"
                        class="w-full sm:w-auto justify-center"
                        color="gray"
                    >
                        Buka Halaman Input
                    </x-filament::button>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-6 text-center">
            <div class="p-3 mb-4 rounded-full bg-success-100 text-success-600 dark:bg-success-900/20 dark:text-success-400">
                <x-heroicon-o-check-circle class="w-8 h-8" />
            </div>
            <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-gray-100">
                Luar Biasa!
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Semua tugas untuk rombel ini sudah terselesaikan 100%.
            </p>
        </div>
    @endif
</div>
