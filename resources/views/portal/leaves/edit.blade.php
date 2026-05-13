@extends('layouts.portal')

@section('title', 'Perbaiki Izin Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div>
        <a href="{{ route('leaves.index') }}" class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2 mb-4 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar
        </a>
        <h1 class="text-4xl font-bold">Perbaiki Izin</h1>
        <p class="text-gray-500">Sesuaikan permohonan Anda berdasarkan catatan penolakan dari sekolah.</p>
    </div>

    <!-- Rejection Note Alert -->
    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl flex gap-4 items-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 15c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <p class="font-bold">Alasan Penolakan:</p>
            <p class="text-sm opacity-90">{{ $leave->rejection_note }}</p>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('leaves.update', $leave->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] p-8 md:p-12 shadow-sm space-y-8">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Student Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Anak</label>
                <select name="student_id" class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('student_id') ring-2 ring-red-500 @enderror">
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ $leave->student_id == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }} ({{ $student->nisn }})
                        </option>
                    @endforeach
                </select>
                @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Type -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Izin</label>
                <div class="flex gap-4">
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" name="type" value="sakit" class="hidden peer" {{ $leave->type === 'sakit' ? 'checked' : '' }}>
                        <div class="bg-gray-50 p-4 rounded-2xl text-center border-2 border-transparent peer-checked:border-primary peer-checked:bg-blue-50 transition-all">
                            <span class="block font-bold">🤒 SAKIT</span>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" name="type" value="izin" class="hidden peer" {{ $leave->type === 'izin' ? 'checked' : '' }}>
                        <div class="bg-gray-50 p-4 rounded-2xl text-center border-2 border-transparent peer-checked:border-primary peer-checked:bg-blue-50 transition-all">
                            <span class="block font-bold">📝 IZIN</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $leave->start_date->format('Y-m-d') }}" class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('start_date') ring-2 ring-red-500 @enderror">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $leave->end_date->format('Y-m-d') }}" class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('end_date') ring-2 ring-red-500 @enderror">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Alasan / Keterangan</label>
                <textarea name="reason" rows="4" placeholder="Jelaskan alasan izin dengan detail..." class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('reason') ring-2 ring-red-500 @enderror">{{ $leave->reason }}</textarea>
                @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Attachment -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Unggah Bukti Baru (Opsional)</label>
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-primary transition-colors cursor-pointer relative group">
                    <input type="file" name="attachment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-gray-400 group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-gray-500">Klik untuk ganti lampiran</p>
                        <p class="text-xs text-gray-400">Biarkan kosong jika tidak ingin mengubah lampiran</p>
                    </div>
                </div>
                @if($leave->attachment)
                    <p class="text-xs text-gray-400 mt-2 italic">* Sudah ada lampiran tersimpan</p>
                @endif
                @error('attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-bold shadow-lg hover:shadow-xl hover:translate-y-[-2px] transition-all">
            Simpan Perubahan & Kirim Ulang
        </button>
    </form>
</div>
@endsection
