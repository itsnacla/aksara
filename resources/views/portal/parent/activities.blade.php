        <div class="space-y-6 md:space-y-8">
            @foreach($children as $child)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-6 space-y-6">
                    <!-- Child Info Header -->
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <span class="text-sm font-bold">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-base text-gray-800 truncate">{{ $child->user->name }}</h3>
                            <p class="text-xs text-gray-400 font-medium">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                        </div>
                    </div>

                    <!-- Ekstrakurikuler -->
                    <div>
                        <h4 class="text-[13px] font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            Ekstrakurikuler yang Diikuti
                        </h4>
                        @php
                            $childEkskuls = $child->extracurriculars()->with(['coordinator.teacher'])->orderBy('kategori', 'asc')->orderBy('nama_ekskul', 'asc')->get();
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse($childEkskuls as $ekskul)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl border border-transparent hover:border-primary/10 hover:bg-primary/5 transition-colors group cursor-pointer">
                                    <div class="min-w-0 flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full {{ $ekskul->kategori === 'wajib' ? 'bg-red-500' : 'bg-green-500' }}"></div>
                                        <div>
                                            <p class="font-bold text-sm text-gray-800 group-hover:text-primary transition-colors truncate">{{ $ekskul->nama_ekskul }}</p>
                                            <p class="text-[11px] font-medium text-gray-400 mt-0.5">{{ $ekskul->coordinator?->nama_lengkap ?? 'Belum ada pembina' }}</p>
                                            @if($ekskul->hari_pelaksanaan)
                                                <p class="text-[11px] font-medium text-primary mt-0.5">{{ is_array($ekskul->hari_pelaksanaan) ? implode(', ', $ekskul->hari_pelaksanaan) : $ekskul->hari_pelaksanaan }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase shrink-0 {{ $ekskul->kategori === 'wajib' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                                        {{ ucwords($ekskul->kategori) }}
                                    </span>
                                </div>
                            @empty
                                <p class="col-span-full text-xs text-gray-400 font-medium py-2 px-1">Tidak mengikuti ekstrakurikuler.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Kokurikuler (P5) -->
                    <div>
                        <h4 class="text-[13px] font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            Kokurikuler (P5) yang Diikuti
                        </h4>
                        @php
                            $childP5 = $academicYear ? $child->p5Groups()->where('academic_year_id', $academicYear->id)->with('project.theme')->get() : collect();
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse($childP5 as $project)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl border border-transparent hover:border-primary/10 hover:bg-primary/5 transition-colors group cursor-pointer">
                                    <div class="min-w-0 flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></div>
                                        <div>
                                            <p class="font-bold text-sm text-gray-800 group-hover:text-primary transition-colors truncate">{{ $project->name }}</p>
                                            <p class="text-[11px] font-medium text-gray-400 mt-0.5">{{ $project->project?->theme?->name ?? 'Tema P5' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="col-span-full text-xs text-gray-400 font-medium py-2 px-1">Tidak mengikuti projek P5.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
