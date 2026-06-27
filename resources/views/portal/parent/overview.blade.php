        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="lg:col-span-2 space-y-6 md:space-y-8">
                {{-- Rekomendasi / Rapor --}}
                <div>
                    <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">Rekomendasi Belajar (Dari Rapor)</h2>
                    @if($publishedRapors->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($publishedRapors as $rapor)
                            <div class="bg-primary rounded-3xl p-6 text-white shadow-sm relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                                <div class="relative z-10 flex items-start gap-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg mb-1">Rekomendasi untuk {{ $rapor->student->user->name }}</h3>
                                        <p class="text-[11px] font-semibold text-white/70 mb-3 uppercase tracking-wider">TA {{ $rapor->academicYear->tahun_ajaran }} ({{ ucfirst($rapor->academicYear->semester) }})</p>
                                        <p class="text-sm text-white/90 leading-relaxed italic">"{{ $rapor->catatan_wali_kelas ?? 'Tetap semangat belajar dan raih prestasi terbaikmu!' }}"</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] text-center">
                            <div class="w-16 h-16 bg-primary/5 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-800 mb-2">Belum Ada Rekomendasi</h3>
                            <p class="text-sm text-gray-500">Rekomendasi belajar akan muncul otomatis setelah Rapor anak Anda dipublikasikan.</p>
                        </div>
                    @endif
                </div>

                {{-- Anak Anda --}}
                <div>
                    <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">Anak Anda</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($children as $child)
                        <div class="bg-white rounded-[20px] p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary overflow-hidden shrink-0 ring-4 ring-primary/5">
                                    @if($child->user->photo)
                                        <img src="{{ asset('storage/' . $child->user->photo) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="font-bold text-sm">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-sm text-gray-800 truncate">{{ $child->user->name }}</h3>
                                    <p class="text-[11px] font-medium text-gray-400 mt-0.5">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                                </div>
                            </div>
                            <!-- View student card icon button -->
                            <button onclick="window.openProfileModal()" class="shrink-0 bg-primary/5 hover:bg-primary/10 text-primary p-2.5 rounded-xl transition-all cursor-pointer" title="Lihat Kartu Pelajar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="space-y-6 md:space-y-8">
                {{-- Quick Actions --}}
                <div class="bg-primary rounded-3xl p-6 text-white shadow-sm relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    <h3 class="font-bold text-[13px] mb-5 uppercase tracking-widest text-white/80 relative z-10">Layanan Cepat</h3>
                    <div class="space-y-3 relative z-10">
                        <a href="{{ route('leaves.create') }}" class="w-full flex items-center justify-between bg-white/10 hover:bg-white/20 p-4 rounded-2xl transition-all group text-left cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <span class="text-sm font-bold">Ajukan Izin Baru</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                        <button onclick="window.openProfileModal()" class="w-full flex items-center justify-between bg-white/10 hover:bg-white/20 p-4 rounded-2xl transition-all group text-left cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                </div>
                                <span class="text-sm font-bold">Kartu Pelajar Anak</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                        <button @click="tab = 'academic'" class="w-full flex items-center justify-between bg-white/10 hover:bg-white/20 p-4 rounded-2xl transition-all group text-left cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                                <span class="text-sm font-bold">Akademik & Nilai</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
