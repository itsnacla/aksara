<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
    <div class="lg:col-span-2 space-y-6 md:space-y-8">
        {{-- Status Anak Hari Ini --}}
        <div>
            <h2 class="text-[15px] font-bold text-gray-800 mb-3 px-1">Kehadiran Anak Hari Ini</h2>
            <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-4 -mx-4 px-4 md:mx-0 md:px-0 md:grid md:grid-cols-2 md:pb-0 hide-scrollbar">
                @foreach($children as $child)
                @php $childAtt = $child->attendances->first(); @endphp
                <div class="snap-center shrink-0 w-72 md:w-auto bg-white rounded-3xl p-5 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_4px_20px_rgba(0,0,0,0.08)] transition-all">
                    <div class="flex items-center gap-4 mb-4">
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
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl mb-4 text-xs">
                        <span class="text-gray-500 font-medium">Presensi Hari Ini</span>
                        @if($childAtt)
                            <span class="font-bold px-2 py-1 rounded-lg {{ $childAtt->status === 'hadir' ? 'bg-green-100 text-green-700' : ($childAtt->status === 'alpa' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600') }}">
                                {{ strtoupper($childAtt->status) }}
                            </span>
                        @else
                            <span class="text-gray-400 italic text-[10px]">Belum Presensi</span>
                        @endif
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('leaves.create') }}?student_id={{ $child->id }}" class="flex items-center justify-center w-full bg-primary/5 hover:bg-primary/10 text-primary py-2.5 rounded-xl font-bold text-[11px] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ajukan Izin Anak
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Riwayat Perizinan --}}
        <div>
            <div class="flex justify-between items-center mb-3 px-1">
                <h2 class="text-[15px] font-bold text-gray-800">Riwayat Perizinan Anak</h2>
                <a href="{{ route('leaves.index') }}" class="text-primary text-xs font-bold hover:underline">Lihat Semua</a>
            </div>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
                <div class="p-10 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Buka menu Izin untuk melihat riwayat.</p>
                    <a href="{{ route('leaves.create') }}" class="mt-4 bg-primary/10 text-primary px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/20 transition-colors">Ajukan Izin Baru</a>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 md:space-y-8">
        {{-- Kehadiran Ring --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] text-center relative overflow-hidden">
            <div class="absolute -left-6 -top-6 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
            <h3 class="font-bold text-[15px] text-gray-800 mb-6 relative z-10">Kehadiran Bulan Ini</h3>
            <div class="relative w-32 h-32 mx-auto mb-5 z-10">
                <svg class="w-32 h-32 transform -rotate-90 drop-shadow-md" viewBox="0 0 36 36">
                    <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#f3f4f6" stroke-width="3"/>
                    <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#005da7" stroke-width="3" stroke-dasharray="{{ $attendancePercentage }}, 100" stroke-linecap="round" class="transition-all duration-1000"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center flex-col">
                    <span class="text-3xl font-black text-gray-800 tracking-tighter">{{ $attendancePercentage }}<span class="text-lg text-gray-400">%</span></span>
                </div>
            </div>
            <p class="text-[11px] font-medium text-gray-400 relative z-10 bg-gray-50 inline-block px-3 py-1 rounded-full">Persentase kehadiran anak-anak Anda</p>
        </div>
    </div>
</div>
