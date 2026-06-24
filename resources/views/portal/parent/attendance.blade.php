<div class="space-y-6 md:space-y-8">
    
    {{-- Kehadiran Ring (Top Banner) --}}
    <div class="bg-white rounded-[2rem] p-6 md:p-8 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] relative overflow-hidden flex flex-col md:flex-row items-center gap-6 md:gap-10">
        <div class="absolute -left-6 -top-6 w-40 h-40 bg-primary/5 rounded-full blur-3xl"></div>
        
        <div class="relative w-32 h-32 shrink-0 z-10">
            <svg class="w-32 h-32 transform -rotate-90 drop-shadow-sm" viewBox="0 0 36 36">
                <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#f3f4f6" stroke-width="3"/>
                <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#005da7" stroke-width="3" stroke-dasharray="{{ $attendancePercentage }}, 100" stroke-linecap="round" class="transition-all duration-1000"/>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center flex-col">
                <span class="text-3xl font-black text-gray-800 tracking-tighter">{{ $attendancePercentage }}<span class="text-lg text-gray-400">%</span></span>
            </div>
        </div>
        
        <div class="text-center md:text-left z-10 flex-1">
            <h3 class="font-bold text-xl md:text-2xl text-gray-800 mb-2">Kehadiran Bulan Ini</h3>
            <p class="text-sm font-medium text-gray-500 mb-5 leading-relaxed max-w-md mx-auto md:mx-0">Persentase di atas merupakan rata-rata kehadiran seluruh anak Anda di bulan ini.</p>
            <span class="text-xs font-bold text-primary bg-primary/10 px-4 py-2 rounded-xl uppercase tracking-wider">Pantau Terus Kehadiran Anak!</span>
        </div>
    </div>

    {{-- Status Anak Hari Ini (Full Width Rows) --}}
    <div>
        <h2 class="text-[15px] font-bold text-gray-800 mb-4 px-1">Kehadiran Anak Hari Ini</h2>
        <div class="space-y-4">
            @foreach($children as $child)
            @php $childAtt = $child->attendances->first(); @endphp
            <div class="bg-white rounded-3xl p-5 md:p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_4px_20px_rgba(0,0,0,0.08)] transition-all flex flex-col md:flex-row md:items-center justify-between gap-5">
                
                {{-- Profile --}}
                <div class="flex items-center gap-4 md:gap-5">
                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary overflow-hidden shrink-0 ring-4 ring-primary/5">
                        @if($child->user->photo)
                            <img src="{{ asset('storage/' . $child->user->photo) }}" class="w-full h-full object-cover">
                        @else
                            <span class="font-bold text-lg md:text-xl">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-base md:text-lg text-gray-800 truncate">{{ $child->user->name }}</h3>
                        <p class="text-xs md:text-sm font-medium text-gray-400 mt-1">{{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }}</p>
                    </div>
                </div>
                
                {{-- Status & Action --}}
                <div class="flex flex-col md:flex-row md:items-center gap-4 shrink-0">
                    <div class="flex items-center gap-3 px-5 py-3 md:py-3.5 bg-gray-50 rounded-2xl min-w-[200px] justify-between border border-gray-100/50">
                        <span class="text-gray-500 font-bold text-xs md:text-sm">Presensi:</span>
                        @if($childAtt)
                            <span class="font-bold text-xs md:text-sm px-3 py-1.5 rounded-xl {{ $childAtt->status === 'hadir' ? 'bg-green-100 text-green-700' : ($childAtt->status === 'alpa' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600') }}">
                                {{ strtoupper($childAtt->status) }}
                            </span>
                        @else
                            <span class="text-gray-400 italic text-xs md:text-sm font-medium">Belum Presensi</span>
                        @endif
                    </div>
                    
                    <a href="{{ route('leaves.create', ['student_id' => $child->id]) }}" class="flex items-center justify-center bg-primary/5 hover:bg-primary/10 text-primary px-6 py-3.5 rounded-2xl font-bold text-sm transition-colors whitespace-nowrap border border-primary/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajukan Izin Anak
                    </a>
                </div>
                
            </div>
            @endforeach
        </div>
    </div>

    {{-- Riwayat Perizinan --}}
    <div>
        <div class="flex justify-between items-center mb-4 px-1">
            <h2 class="text-[15px] font-bold text-gray-800">Riwayat Perizinan Anak</h2>
            <a href="{{ route('leaves.index') }}" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
                Lihat Semua 
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
            @if($recentLeaves->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($recentLeaves as $leave)
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl {{ $leave->type === 'sakit' ? 'bg-orange-50 text-orange-500' : 'bg-blue-50 text-blue-500' }} flex items-center justify-center shrink-0">
                                @if($leave->type === 'sakit')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-sm md:text-base text-gray-800">{{ strtoupper($leave->type) }} - {{ $leave->student->user->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $leave->start_date->format('d M Y') }} {{ $leave->start_date != $leave->end_date ? '- ' . $leave->end_date->format('d M Y') : '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between md:justify-end border-t md:border-0 border-gray-100 pt-3 md:pt-0 mt-2 md:mt-0">
                            <span class="text-xs font-bold text-gray-500 md:hidden block">Status:</span>
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-gray-100 text-gray-600',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $statusClasses[$leave->status] }}">
                                {{ strtoupper($leave->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="p-10 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Belum ada riwayat perizinan.</p>
                    <a href="{{ route('leaves.create') }}" class="mt-4 inline-block bg-primary/10 text-primary px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/20 transition-colors">Ajukan Izin Baru</a>
                </div>
            @endif
        </div>
    </div>
</div>
