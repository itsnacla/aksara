<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
    <div class="lg:col-span-2 space-y-6 md:space-y-8">
        {{-- Presensi Hari Ini --}}
        <div class="bg-white rounded-[20px] p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-green-50 rounded-full opacity-50"></div>
            <div class="flex items-center gap-3 mb-5 relative z-10">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-[13px] text-gray-500 uppercase tracking-widest">Presensi Hari Ini</h3>
            </div>
            @if($attendance)
                <div class="flex items-center justify-between relative z-10 bg-gray-50 p-4 rounded-2xl">
                    <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $attendance->status === 'hadir' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-600' }}">
                        {{ strtoupper($attendance->status) }}
                    </span>
                    <div class="flex items-center gap-1.5 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-sm font-semibold">{{ $attendance->created_at->format('H:i') }} WIB</span>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 p-4 rounded-2xl flex items-center justify-center relative z-10">
                    <p class="text-xs text-gray-400 font-medium">Belum ada catatan kehadiran hari ini.</p>
                </div>
            @endif
        </div>

        {{-- Riwayat Izin Terakhir --}}
        <div>
            <div class="flex justify-between items-center mb-3 px-1">
                <h2 class="text-[15px] font-bold text-gray-800">Riwayat Perizinan</h2>
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
            <p class="text-[11px] font-medium text-gray-400 relative z-10 bg-gray-50 inline-block px-3 py-1 rounded-full">Persentase kehadiran Anda</p>
        </div>
    </div>
</div>
