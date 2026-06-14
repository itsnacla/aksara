@extends('layouts.portal')

@section('title', 'Daftar Izin Siswa')

@section('content')
<div class="space-y-8" x-data="{ 
    showModal: {{ $errors->any() && old('_method') == 'PUT' ? 'true' : 'false' }}, 
    modalData: { id: @js(old('id', '')), student: '', type: @js(old('type', '')), dates: '', reason: @js(old('reason', '')), attachment: '', status: 'rejected', rejection_note: '', start_date: @js(old('start_date', '')), end_date: @js(old('end_date', '')) },
    createStudentId: '',
    selectedFile: null,
    editFile: null,
    openModal(id, student, type, start_date, end_date, formatted_dates, reason, attachment, status, rejection_note) {
        this.modalData = { id, student, type, dates: formatted_dates, start_date, end_date, reason, attachment, status, rejection_note };
        this.editFile = null;
        this.showModal = true;
    }
}">
    {{-- Hero Header --}}
    <header class="flex justify-between items-center bg-primary rounded-3xl p-6 md:p-8 text-white shadow-sm relative overflow-hidden">
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
        
        <div class="relative z-10 flex-1">
            <h1 class="text-2xl md:text-3xl font-bold leading-tight mb-2">Daftar Izin & Sakit</h1>
            <p class="text-sm md:text-base text-white/80 max-w-md">Kelola riwayat permohonan izin dan sakit untuk semua anak Anda di satu tempat.</p>
        </div>
        
        @if(auth()->user()->hasRole('wali'))
        <div class="relative z-10 ml-4">
            <a href="{{ route('leaves.create') }}" class="bg-white/20 hover:bg-white/30 text-white backdrop-blur-sm px-5 py-2.5 rounded-2xl font-bold text-sm transition-all flex items-center gap-2 cursor-pointer border border-white/10 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline">Buat Izin Baru</span>
                <span class="md:hidden">Baru</span>
            </a>
        </div>
        @endif
    </header>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-[2rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap md:whitespace-normal">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-widest">
                        <th class="px-6 md:px-8 py-4">Siswa</th>
                        <th class="px-6 md:px-8 py-4">Tipe</th>
                        <th class="px-6 md:px-8 py-4">Tanggal</th>
                        <th class="px-6 md:px-8 py-4">Status</th>
                        <th class="px-6 md:px-8 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 md:px-8 py-6">
                            <div class="font-bold">{{ $leave->student->user->name }}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                NISN: {{ $leave->student->nisn }} | 
                                <span class="text-primary font-bold">{{ $leave->studyGroup?->nama_rombel ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 md:px-8 py-6">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $leave->type === 'sakit' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ strtoupper($leave->type) }}
                            </span>
                        </td>
                        <td class="px-6 md:px-8 py-6">
                            <div class="text-sm">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 md:px-8 py-6">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-gray-100 text-gray-600',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusClasses[$leave->status] }}">
                                {{ strtoupper($leave->status) }}
                            </span>
                            @if($leave->status === 'rejected' && $leave->rejection_note)
                                <p class="text-[10px] text-red-500 mt-2 italic font-medium">Ket: {{ $leave->rejection_note }}</p>
                            @endif
                        </td>
                        <td class="px-6 md:px-8 py-6 flex gap-3 min-w-[120px]">
                            @if($leave->status === 'rejected')
                                <button @click="openModal('{{ $leave->id }}', '{{ addslashes($leave->student->user->name) }}', '{{ strtolower($leave->type) }}', '{{ $leave->start_date->format('Y-m-d') }}', '{{ $leave->end_date->format('Y-m-d') }}', '{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $leave->reason)) }}', '{{ $leave->attachment ? asset('storage/' . $leave->attachment) : '' }}', '{{ $leave->status }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $leave->rejection_note)) }}')" class="text-primary hover:underline text-sm font-bold flex items-center gap-1 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Perbaiki
                                </button>
                            @else
                                <button @click="openModal('{{ $leave->id }}', '{{ addslashes($leave->student->user->name) }}', '{{ strtoupper($leave->type) }}', '{{ $leave->start_date->format('Y-m-d') }}', '{{ $leave->end_date->format('Y-m-d') }}', '{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}', '{{ addslashes(str_replace(["\r", "\n"], ' ', $leave->reason)) }}', '{{ $leave->attachment ? asset('storage/' . $leave->attachment) : '' }}', '{{ $leave->status }}', '')" class="text-gray-400 hover:text-primary text-sm font-bold cursor-pointer transition-colors">Detail</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 md:px-8 py-12 text-center text-gray-400 italic">
                            Belum ada riwayat izin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
        
        <!-- Modal Content -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-3xl shadow-xl w-full max-w-lg relative z-10 overflow-hidden border border-gray-100">
             
            <!-- Header -->
            <div class="px-8 py-6 bg-gray-50/80 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold font-heading text-gray-800">Detail Permohonan</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-red-500 bg-white hover:bg-red-50 p-2 rounded-xl transition-all cursor-pointer shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <!-- Read-only Body (for pending/approved) -->
            <div x-show="modalData.status !== 'rejected'" class="p-8 space-y-6">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nama Siswa</p>
                    <p class="font-semibold text-gray-800" x-text="modalData.student"></p>
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Jenis</p>
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold inline-block mt-1 uppercase" x-text="modalData.type"></span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tanggal</p>
                        <p class="font-medium text-gray-800 text-sm mt-1" x-text="modalData.dates"></p>
                    </div>
                </div>

                <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100/50">
                    <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-2">Alasan/Keterangan</p>
                    <p class="text-sm text-gray-700 leading-relaxed" x-text="modalData.reason"></p>
                </div>

                <!-- Attachment Area -->
                <div x-show="modalData.attachment">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Lampiran Bukti</p>
                    
                    <!-- If it's an image -->
                    <div x-show="modalData.attachment.match(/\.(jpeg|jpg|gif|png)$/i) != null">
                        <a :href="modalData.attachment" target="_blank" class="block rounded-2xl overflow-hidden border-2 border-gray-100 hover:border-primary transition-colors relative group">
                            <img :src="modalData.attachment" class="w-full h-48 object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="bg-white text-gray-800 text-xs font-bold px-4 py-2 rounded-xl shadow-lg">Lihat Penuh</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- If it's a PDF or other file -->
                    <div x-show="modalData.attachment.match(/\.(jpeg|jpg|gif|png)$/i) == null">
                        <a :href="modalData.attachment" target="_blank" class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 hover:border-primary hover:bg-blue-50 transition-all group">
                            <div class="w-12 h-12 bg-gray-100 group-hover:bg-white rounded-xl flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-700 group-hover:text-primary transition-colors">Lihat Dokumen</p>
                                <p class="text-xs text-gray-400 mt-0.5">Klik untuk membuka di tab baru</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div x-show="!modalData.attachment" class="bg-gray-50 border border-dashed border-gray-200 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-xs text-gray-400 font-medium">Tidak ada lampiran</p>
                </div>
            </div>

            <!-- Edit Form (for rejected) -->
            <div x-show="modalData.status === 'rejected'" class="p-8 space-y-6">
                <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h4 class="font-bold text-sm">Permohonan Ditolak</h4>
                        <p class="text-xs mt-1" x-text="modalData.rejection_note"></p>
                        <p class="text-[11px] mt-2 italic">Silakan perbaiki data di bawah ini dan kirim ulang.</p>
                    </div>
                </div>

                <form :action="'{{ url('leaves') }}/' + modalData.id" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="modalData.id">
                    
                    @if($errors->any() && old('_method') == 'PUT')
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 text-sm font-medium">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Siswa</p>
                        <p class="font-bold text-gray-800" x-text="modalData.student"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Perizinan</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="sakit" class="peer sr-only" x-model="modalData.type" required>
                                <div class="p-4 rounded-xl border-2 border-gray-100 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:bg-gray-50 transition-all">
                                    <span class="block font-bold">SAKIT</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="izin" class="peer sr-only" x-model="modalData.type" required>
                                <div class="p-4 rounded-xl border-2 border-gray-100 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-all">
                                    <span class="block font-bold">IZIN</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Mulai</label>
                            <input type="date" name="start_date" x-model="modalData.start_date" required class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Selesai</label>
                            <input type="date" name="end_date" x-model="modalData.end_date" required class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Alasan Lengkap</label>
                        <textarea name="reason" x-model="modalData.reason" rows="3" required placeholder="Tuliskan alasan..." class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-2.5 resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Ganti Bukti (Opsional)</label>
                        <div x-show="modalData.attachment" class="mb-2">
                            <a :href="modalData.attachment" target="_blank" class="text-xs text-primary hover:underline">Lihat Bukti Saat Ini</a>
                        </div>
                        <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" @change="editFile = $event.target.files[0] ? $event.target.files[0].name : null" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <p x-show="editFile" x-text="'Terpilih: ' + editFile" class="text-xs text-primary font-bold mt-2" style="display: none;"></p>
                    </div>
                    
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="showModal = false" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="w-2/3 bg-primary hover:bg-primary/90 text-white font-bold py-3 rounded-xl transition-all shadow-md">Simpan Perbaikan</button>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div x-show="modalData.status !== 'rejected'" class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button @click="showModal = false" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-100 transition-colors cursor-pointer shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
