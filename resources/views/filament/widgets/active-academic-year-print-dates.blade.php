<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Pengaturan Tanggal Cetak Dokumen
        </x-slot>

        <x-slot name="description">
            Tentukan tanggal yang wajib dicetak pada dokumen-dokumen untuk Tahun Ajaran yang sedang Aktif saat ini.
        </x-slot>

        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit">
                    Simpan Pengaturan
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
