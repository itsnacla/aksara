<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>

    @livewire(\App\Filament\Widgets\PerkembanganNilaiChart::class, ['studyGroupId' => $study_group_id, 'studentId' => $student_id])
    
    @livewire(\App\Filament\Widgets\PerkembanganNilaiTable::class, ['studyGroupId' => $study_group_id, 'studentId' => $student_id])
</x-filament-panels::page>
