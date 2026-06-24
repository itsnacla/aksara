        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="lg:col-span-2 space-y-6 md:space-y-8">
                {{-- E-Raport Section --}}
                <div>
                    <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">E-Raport Digital</h2>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Rapor Akademik</p>
                                <p class="text-[11px] text-gray-400">Unduh dan tinjau rapor putra-putri Anda</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            @foreach($children as $child)
                            <div class="border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                        <span class="text-xs font-bold">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-sm truncate">{{ $child->user->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @php
                                        $rapors = \App\Models\StudentRapor::with('academicYear')
                                                    ->where('student_id', $child->id)
                                                    ->where('is_published', true)
                                                    ->orderBy('academic_year_id', 'desc')
                                                    ->get();
                                    @endphp
                                    @forelse($rapors as $rapor)
                                        <a href="{{ route('print.rapor', ['student' => $child, 'academic_year_id' => $rapor->academic_year_id]) }}" target="_blank"
                                            class="flex items-center justify-between p-3 bg-gray-50 hover:bg-primary/5 rounded-xl transition-colors group">
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-700 group-hover:text-primary transition-colors">{{ $rapor->academicYear->tahun_ajaran }}</p>
                                                <p class="text-[10px] text-gray-400">Semester {{ $rapor->academicYear->semester }}</p>
                                            </div>
                                            <div class="flex items-center gap-1 text-primary shrink-0">
                                                <span class="text-[10px] font-semibold">Lihat</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="col-span-full text-[11px] text-gray-400 font-medium py-2">Belum ada rapor yang dipublikasikan.</p>
                                    @endforelse
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6 md:space-y-8">
                {{-- Nilai Terbaru --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-[15px] text-gray-800">Nilai Terbaru</h3>
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse($recentGrades as $grade)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-2 h-2 rounded-full bg-primary shrink-0"></div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $grade->subject->nama_mapel }}</p>
                                    <p class="text-[11px] text-gray-400 font-medium truncate">{{ $grade->student->user->name }}</p>
                                </div>
                            </div>
                            <span class="bg-gray-50 group-hover:bg-primary/5 text-gray-800 group-hover:text-primary font-black text-[15px] w-12 h-12 rounded-[14px] flex items-center justify-center shrink-0 transition-colors">
                                {{ $grade->nilai_uts ?? $grade->nilai_tugas ?? 0 }}
                            </span>
                        </div>
                        @empty
                        <div class="py-6 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2-2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                            <p class="text-gray-400 text-xs font-medium">Belum ada nilai baru.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
