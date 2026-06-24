<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card: Clustering -->
        <a href="{{ \App\Filament\Pages\ClusteringSiswa::getUrl() }}" class="block">
            <x-filament::card class="h-full hover:border-primary-500 hover:ring-1 hover:ring-primary-500 transition duration-200">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="p-3 bg-primary-100 dark:bg-primary-900 rounded-lg">
                        <x-filament::icon
                            icon="heroicon-o-rectangle-group"
                            class="w-8 h-8 text-primary-600 dark:text-primary-400"
                        />
                    </div>
                    <h2 class="text-xl font-bold">Clustering Siswa</h2>
                </div>
                <p class="text-gray-500 dark:text-gray-400">
                    Gunakan algoritma AI untuk mengelompokkan karakteristik belajar siswa dalam suatu rombongan belajar berdasarkan pola nilai dan absensi mereka.
                </p>
                <div class="mt-4 flex items-center text-primary-600 font-semibold">
                    <span>Mulai Analisis</span>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="w-4 h-4 ml-2" />
                </div>
            </x-filament::card>
        </a>

        <!-- Card: Prediksi Dropout -->
        <a href="{{ \App\Filament\Pages\PrediksiDropout::getUrl() }}" class="block">
            <x-filament::card class="h-full hover:border-danger-500 hover:ring-1 hover:ring-danger-500 transition duration-200">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="p-3 bg-danger-100 dark:bg-danger-900 rounded-lg">
                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="w-8 h-8 text-danger-600 dark:text-danger-400"
                        />
                    </div>
                    <h2 class="text-xl font-bold">Prediksi Dropout (EWS)</h2>
                </div>
                <p class="text-gray-500 dark:text-gray-400">
                    Early Warning System (EWS) untuk memprediksi tingkat risiko seorang siswa tinggal kelas atau putus sekolah berdasarkan jejak akademiknya.
                </p>
                <div class="mt-4 flex items-center text-danger-600 font-semibold">
                    <span>Mulai Prediksi</span>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="w-4 h-4 ml-2" />
                </div>
            </x-filament::card>
        </a>

    </div>
</x-filament-panels::page>
