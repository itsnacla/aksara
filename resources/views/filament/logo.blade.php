@php
    $school = \Illuminate\Support\Facades\Schema::hasTable('school_settings') ? \App\Models\SchoolSetting::first() : null;
    $schoolName = $school->name ?? 'Aksara.';
    $logoUrl = $school && $school->logo ? \Illuminate\Support\Facades\Storage::url($school->logo) : asset('images/logo-nobg.png');
@endphp
<div class="flex items-center gap-3">
    <img src="{{ $logoUrl }}" alt="{{ $schoolName }}" class="object-contain" style="height: 2.5rem;">
    <span class="font-bold text-xl tracking-tight text-primary-600 dark:text-primary-400">{{ $schoolName }}</span>
</div>
