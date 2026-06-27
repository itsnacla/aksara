        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="lg:col-span-2 space-y-6 md:space-y-8">
                {{-- E-Raport --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] bg-primary relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-primary/5 rounded-full blur-xl -mr-10 -mt-10"></div>
                    <div class="flex items-center gap-4 mb-5 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center shadow-sm shadow-primary/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-[15px] text-gray-800">E-Raport Digital</h3>
                            <p class="text-[11px] font-medium text-gray-500">Akses laporan hasil belajar</p>
                        </div>
                    </div>
                    @php
                        $studentSelf = auth()->user()->student;
                        $rapors = $studentSelf ? \App\Models\StudentRapor::with('academicYear')
                                        ->where('student_id', $studentSelf->id)
                                        ->where('is_published', true)
                                        ->orderBy('academic_year_id', 'desc')
                                        ->get() : collect();
                    @endphp
                    <div class="space-y-3 relative z-10">
                        @forelse($rapors as $rapor)
                            <a href="{{ route('print.rapor', ['student' => $studentSelf, 'academic_year_id' => $rapor->academic_year_id]) }}" target="_blank"
                                class="flex items-center justify-between p-4 bg-white border border-white hover:border-primary/20 shadow-sm hover:shadow-md rounded-2xl transition-all group">
                                <div>
                                    <p class="text-xs font-bold text-gray-800 group-hover:text-primary transition-colors">{{ $rapor->academicYear->tahun_ajaran }}</p>
                                    <p class="text-[10px] font-medium text-gray-400">Semester {{ $rapor->academicYear->semester }}</p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                                    <svg class="w-4 h-4 translate-x-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-6 bg-white/50 rounded-2xl border border-white">
                                <p class="text-[11px] text-gray-400 font-medium">Belum ada rapor yang dipublikasikan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Transkrip Nilai Lengkap --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-sm shadow-emerald-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-[15px] text-gray-800">Transkrip Nilai Akademik</h3>
                            <p class="text-[11px] font-medium text-gray-500">Kumpulan riwayat nilai dari seluruh semester</p>
                        </div>
                    </div>
                    
                    @php
                        $transcriptData = $studentSelf ? \App\Models\Grade::with(['subject', 'academicYear'])
                            ->where('student_id', $studentSelf->id)
                            ->get()
                            ->groupBy(function($g) {
                                return $g->academicYear->tahun_ajaran . ' - Semester ' . $g->academicYear->semester;
                            })->sortKeysDesc() : collect();
                    @endphp

                    <div class="space-y-4" x-data="{ activeAccordion: null }">
                        @forelse($transcriptData as $semesterName => $grades)
                            @php
                                $avgSemester = round($grades->avg(fn($g) => ($g->nilai_tugas + $g->nilai_uts + $g->nilai_uas) / 3), 1);
                                $accordionId = Str::slug($semesterName);
                            @endphp
                            <div class="border border-gray-100 rounded-2xl overflow-hidden bg-gray-50/30">
                                <button @click="activeAccordion = activeAccordion === '{{ $accordionId }}' ? null : '{{ $accordionId }}'"
                                        class="w-full flex items-center justify-between p-4 font-bold text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="text-gray-800 font-heading text-xs">{{ $semesterName }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] bg-primary/10 text-primary font-bold">Rata-rata: {{ $avgSemester }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeAccordion === '{{ $accordionId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                <div x-show="activeAccordion === '{{ $accordionId }}'" class="border-t border-gray-100 bg-white p-4">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-[11px]">
                                            <thead>
                                                <tr class="text-gray-400 border-b border-gray-100 font-bold uppercase tracking-wider text-[9px]">
                                                    <th class="py-2">Mata Pelajaran</th>
                                                    <th class="py-2 text-center">Tugas</th>
                                                    <th class="py-2 text-center">UTS</th>
                                                    <th class="py-2 text-center">UAS</th>
                                                    <th class="py-2 text-center font-bold text-primary">Akhir</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50 font-medium">
                                                @foreach($grades as $g)
                                                    @php
                                                        $finalGrade = round(($g->nilai_tugas + $g->nilai_uts + $g->nilai_uas) / 3);
                                                    @endphp
                                                    <tr class="text-gray-700 hover:bg-gray-50/50">
                                                        <td class="py-3 font-bold text-gray-800">{{ $g->subject->nama_mapel }}</td>
                                                        <td class="py-3 text-center text-gray-500">{{ $g->nilai_tugas }}</td>
                                                        <td class="py-3 text-center text-gray-500">{{ $g->nilai_uts }}</td>
                                                        <td class="py-3 text-center text-gray-500">{{ $g->nilai_uas }}</td>
                                                        <td class="py-3 text-center font-black text-xs text-primary bg-primary/5 rounded-lg">{{ $finalGrade }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 bg-white/50 rounded-2xl border border-white">
                                <p class="text-[11px] text-gray-400 font-medium">Belum ada transkrip nilai.</p>
                            </div>
                        @endforelse
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
                                    <p class="text-[11px] text-gray-400 font-medium truncate">Tugas / UTS</p>
                                </div>
                            </div>
                            <span class="bg-gray-50 group-hover:bg-primary/5 text-gray-800 group-hover:text-primary font-black text-[15px] w-12 h-12 rounded-[14px] flex items-center justify-center shrink-0 transition-colors">
                                {{ $grade->nilai_uts ?? $grade->nilai_tugas ?? 0 }}
                            </span>
                        </div>
                        @empty
                        <div class="py-6 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                            <p class="text-gray-400 text-xs font-medium">Belum ada nilai baru.</p>
                        </div>
                        @endforelse
                    </div>
                    @if($gradeAverage > 0)
                    <div class="mt-5 pt-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/50 -mx-6 -mb-6 px-6 py-4 rounded-b-3xl">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Rata-rata</span>
                        <span class="font-black text-primary text-xl">{{ $gradeAverage }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
