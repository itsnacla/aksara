        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="lg:col-span-2 space-y-6 md:space-y-8">
                {{-- Ekskul --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <h3 class="font-bold text-[15px] text-gray-800 mb-5">Ekstrakurikuler Anak</h3>
                    <div class="space-y-3">
                        @forelse($extracurriculars->take(5) as $ekskul)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl hover:bg-primary/5 transition-colors group cursor-pointer">
                            <div class="min-w-0 flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full {{ $ekskul->kategori === 'wajib' ? 'bg-red-500' : 'bg-green-500' }}"></div>
                                <div>
                                    <p class="font-bold text-sm text-gray-800 group-hover:text-primary transition-colors truncate">{{ $ekskul->nama_ekskul }}</p>
                                    <p class="text-[11px] font-medium text-gray-400 mt-0.5">{{ $ekskul->coordinator?->nama_lengkap ?? 'Belum ada pembina' }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase shrink-0 {{ $ekskul->kategori === 'wajib' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                                {{ ucwords($ekskul->kategori) }}
                            </span>
                        </div>
                        @empty
                        <div class="py-6 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
                            <p class="text-gray-400 text-[11px] font-medium">Belum ada ekskul.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <div class="space-y-6 md:space-y-8">
                {{-- Placeholder for other activities --}}
                <div class="bg-primary/5 rounded-3xl p-6 border border-primary/10 text-center">
                    <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-sm mb-2">Aktivitas Tambahan</h3>
                    <p class="text-xs text-gray-500">Keterlibatan pada OSIS atau kepanitiaan akan tampil di sini.</p>
                </div>
            </div>
        </div>
