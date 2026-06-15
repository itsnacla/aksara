<div class="flex flex-col gap-4">
    @if(count($tanggungan) > 0)
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Berikut adalah rincian tugas yang belum selesai untuk rombel ini:
        </p>
        
        <div class="grid gap-3">
            @foreach($tanggungan as $item)
                <div class="flex items-center justify-between p-3 border rounded-lg shadow-sm bg-white dark:bg-gray-800 dark:border-gray-700">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $item['title'] }}
                        </h4>
                        <p class="text-sm text-warning-600 dark:text-warning-400 font-medium">
                            {{ $item['text'] }}
                        </p>
                    </div>
                    <a href="{{ $item['url'] }}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-white transition bg-primary-600 rounded-lg shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:focus:ring-offset-gray-900">
                        Pergi ke halaman
                        <x-heroicon-m-arrow-right class="w-4 h-4 ml-1.5" />
                    </a>
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
