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
                                        <h3 class="font-bold text-lg mb-1">Rekomendasi untukmu</h3>
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
                            <p class="text-sm text-gray-500">Rekomendasi belajar akan muncul otomatis setelah Rapor kamu dipublikasikan.</p>
                        </div>
                    @endif
                </div>

                {{-- Jadwal Hari Ini --}}
                <div>
                    <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">Jadwal Hari Ini</h2>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
                        @forelse($todaySchedules as $schedule)
                        <div class="flex items-stretch p-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                            <div class="flex flex-col items-center justify-center shrink-0 w-16 bg-primary/5 rounded-xl mr-4 py-2">
                                <p class="text-[13px] font-bold text-primary">{{ $schedule->startTimeSlot?->waktu_mulai?->format('H:i') }}</p>
                                <p class="text-[10px] text-primary/60 font-medium">{{ $schedule->endTimeSlot?->waktu_selesai?->format('H:i') }}</p>
                            </div>
                            <div class="min-w-0 flex-1 flex flex-col justify-center py-1">
                                <p class="font-bold text-sm text-gray-800 truncate mb-1">{{ $schedule->subject?->nama_mapel ?? '-' }}</p>
                                <div class="flex items-center gap-1.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <p class="text-[11px] font-medium truncate">{{ $schedule->teacher?->user?->name ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-10 text-center flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">Tidak ada jadwal hari ini.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <div class="space-y-6 md:space-y-8">
                {{-- Info Singkat Rombel --}}
                <div class="bg-white rounded-[20px] p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-50 rounded-full opacity-50"></div>
                    <div class="flex items-center gap-3 mb-5 relative z-10">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="font-bold text-[13px] text-gray-500 uppercase tracking-widest">Rombel Aktif</h3>
                    </div>
                    <div class="relative z-10">
                        <p class="font-black text-gray-800 text-lg">{{ $studyGroup->nama_rombel ?? 'Belum Terdaftar' }}</p>
                        @if($studyGroup && $studyGroup->waliKelas)
                            <div class="flex items-center gap-2 mt-2 text-gray-500 bg-gray-50 p-2.5 rounded-xl">
                                <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <p class="text-[11px] font-medium truncate w-full">{{ $studyGroup->waliKelas->nama_lengkap ?? '-' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
