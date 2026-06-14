@extends('layouts.portal')

@section('title', 'Buat Izin Baru')

@section('content')
<div class="space-y-8" x-data="{ selectedFile: null }">
    {{-- Hero Header --}}
    <header class="flex justify-between items-center bg-primary rounded-3xl p-6 md:p-8 text-white shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
        
        <div class="relative z-10 flex-1">
            <h1 class="text-2xl md:text-3xl font-bold leading-tight mb-2">Ajukan Izin Baru</h1>
            <p class="text-sm md:text-base text-white/80 max-w-md">Ajukan permohonan izin atau sakit untuk anak Anda dengan mengisi formulir di bawah ini.</p>
        </div>
    </header>

    <div class="bg-white rounded-[2rem] shadow-sm overflow-hidden p-6 md:p-8">
        <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto">
            @csrf
            
            @if($errors->any())
                <div class="mb-8 bg-red-50 text-red-600 p-5 rounded-2xl border border-red-100 text-sm font-medium">
                    <div class="flex items-center gap-3 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <h4 class="font-bold">Gagal Menyimpan</h4>
                    </div>
                    <ul class="list-disc pl-8 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Anak <span class="text-red-500">*</span></label>
                    <select name="student_id" required class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-3">
                        <option value="">-- Pilih Anak --</option>
                        @foreach($children as $child)
                            <option value="{{ $child->id }}" {{ old('student_id', request('student_id')) == $child->id ? 'selected' : '' }}>{{ $child->user->name }} ({{ $child->currentStudyGroup()?->nama_rombel ?? 'Tanpa Rombel' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Perizinan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="sakit" class="peer sr-only" required {{ old('type') == 'sakit' ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-gray-100 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:bg-gray-50 transition-all">
                                <span class="block font-bold text-gray-700 peer-checked:text-orange-700">SAKIT</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="izin" class="peer sr-only" required {{ old('type') == 'izin' ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-gray-100 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-all">
                                <span class="block font-bold text-gray-700 peer-checked:text-blue-700">IZIN</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" value="{{ old('start_date', date('Y-m-d')) }}" class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" value="{{ old('end_date', date('Y-m-d')) }}" class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-3">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="4" required placeholder="Tuliskan alasan dengan jelas (minimal 10 karakter)..." class="w-full rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20 transition-all text-sm py-3 resize-none">{{ old('reason') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Bukti / Surat Keterangan (Opsional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-2xl hover:border-primary/50 transition-colors relative" :class="{'border-primary bg-primary/5': selectedFile}">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                    <span>Pilih Dokumen</span>
                                    <input id="file-upload" name="attachment" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf" @change="selectedFile = $event.target.files[0] ? $event.target.files[0].name : null">
                                </label>
                                <p class="pl-1">atau seret ke sini</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF maks. 5MB</p>
                            <p x-show="selectedFile" x-text="'Terpilih: ' + selectedFile" class="text-sm text-primary font-bold mt-3 bg-white px-3 py-1 rounded-lg inline-block border border-primary/20 shadow-sm" style="display: none;"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-10 flex gap-4 border-t border-gray-100 pt-8">
                <a href="{{ route('leaves.index') }}" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3.5 rounded-xl transition-all text-center flex items-center justify-center">Batal</a>
                <button type="submit" class="w-2/3 bg-primary hover:bg-primary/90 text-white font-bold py-3.5 rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
