@extends('layouts.portal')

@section('title', 'Daftar Izin Siswa')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold">Daftar Izin</h1>
            <p class="text-gray-500">Riwayat permohonan izin dan sakit siswa.</p>
        </div>
        @if(auth()->user()->hasRole('wali'))
        <a href="{{ route('leaves.create') }}" class="bg-primary text-white px-6 py-3 rounded-full font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Izin Baru
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-[2rem] shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-widest">
                    <th class="px-8 py-4">Siswa</th>
                    <th class="px-8 py-4">Tipe</th>
                    <th class="px-8 py-4">Tanggal</th>
                    <th class="px-8 py-4">Status</th>
                    <th class="px-8 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($leaves as $leave)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-8 py-6">
                        <div class="font-bold">{{ $leave->student->user->name }}</div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter">
                            NISN: {{ $leave->student->nisn }} | 
                            <span class="text-primary font-bold">{{ $leave->studyGroup?->nama_rombel ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $leave->type === 'sakit' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ strtoupper($leave->type) }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="text-sm">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-8 py-6">
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
                    <td class="px-8 py-6 flex gap-3">
                        @if($leave->status === 'rejected')
                            <a href="{{ route('leaves.edit', $leave->id) }}" class="text-primary hover:underline text-sm font-bold flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Perbaiki
                            </a>
                        @else
                            <button class="text-gray-400 hover:text-primary text-sm font-bold">Detail</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-12 text-center text-gray-400 italic">
                        Belum ada riwayat izin.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
