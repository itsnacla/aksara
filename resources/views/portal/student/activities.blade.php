        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="lg:col-span-2 space-y-6 md:space-y-8">
                {{-- Ekskul --}}
                <div>
                    <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">Ekstrakurikuler</h2>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-5">
                        <div class="space-y-3">
                            @forelse($extracurriculars as $ekskul)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl hover:bg-primary/5 transition-colors group cursor-pointer">
                                <div class="min-w-0 flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full {{ $ekskul->kategori === 'wajib' ? 'bg-red-500' : 'bg-green-500' }}"></div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800 group-hover:text-primary transition-colors truncate">{{ $ekskul->nama_ekskul }}</p>
                                        <p class="text-[11px] font-medium text-gray-400 mt-0.5">{{ $ekskul->coordinator?->nama_lengkap ?? 'Belum ada pembina' }}</p>
                                        @if($ekskul->hari_pelaksanaan)
                                            <p class="text-[11px] font-medium text-primary mt-0.5">{{ implode(', ', $ekskul->hari_pelaksanaan) }}</p>
                                        @endif
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
            </div>
            
            <div class="lg:col-span-1 space-y-6 md:space-y-8">
                {{-- Kokurikuler --}}
                <div>
                    <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">Kokurikuler (P5)</h2>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-5">
                        <div class="space-y-3">
                            @if(isset($p5Projects))
                                @forelse($p5Projects as $project)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl hover:bg-primary/5 transition-colors group cursor-pointer">
                                    <div class="min-w-0 flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></div>
                                        <div>
                                            <p class="font-bold text-sm text-gray-800 group-hover:text-primary transition-colors truncate">{{ $project->name }}</p>
                                            <p class="text-[11px] font-medium text-gray-400 mt-0.5">{{ $project->project?->theme?->name ?? 'Tema P5' }}</p>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="py-6 flex flex-col items-center justify-center text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
                                    <p class="text-gray-400 text-[11px] font-medium">Belum ada projek P5.</p>
                                </div>
                                @endforelse
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
