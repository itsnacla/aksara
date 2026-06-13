@extends('layouts.portal')

@section('title', 'Buat Izin Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div>
        <a href="{{ route('leaves.index') }}" class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2 mb-4 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar
        </a>
        <h1 class="text-4xl font-bold">Buat Izin Baru</h1>
        <p class="text-gray-500">Ajukan permohonan izin atau keterangan sakit untuk anak Anda.</p>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2rem] p-8 md:p-12 shadow-sm space-y-8">
        @csrf
        
        <div class="space-y-6">
            <!-- Student Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Anak</label>
                <select name="student_id" class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('student_id') ring-2 ring-red-500 @enderror">
                    <option value="">-- Pilih --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->nisn }})</option>
                    @endforeach
                </select>
                @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Type -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Izin</label>
                <div class="flex gap-4">
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" name="type" value="sakit" class="hidden peer" checked>
                        <div class="bg-gray-50 p-4 rounded-2xl text-center border-2 border-transparent peer-checked:border-primary peer-checked:bg-blue-50 transition-all">
                            <span class="block font-bold">🤒 SAKIT</span>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" name="type" value="izin" class="hidden peer">
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
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('start_date') ring-2 ring-red-500 @enderror">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('end_date') ring-2 ring-red-500 @enderror">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Alasan / Keterangan</label>
                <textarea name="reason" rows="4" placeholder="Jelaskan alasan izin dengan detail..." class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary transition-all @error('reason') ring-2 ring-red-500 @enderror"></textarea>
                @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Attachment -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Unggah Bukti (Opsional)</label>
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-primary transition-colors cursor-pointer relative group">
                    <input type="file" name="attachment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-gray-400 group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-gray-500">Klik untuk unggah foto surat atau keterangan</p>
                        <p class="text-xs text-gray-400">JPG, PNG (Maks 2MB)</p>
                    </div>
                </div>
                @error('attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-bold shadow-sm hover:shadow-sm hover:translate-y-[-2px] transition-all">
            Kirim Permohonan Izin
        </button>
    </form>
</div>
@endsection
