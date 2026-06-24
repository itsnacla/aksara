<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->batchForm }}

        @if(!empty($allGrades))
            {{-- Native Filament pagination --}}
            @if($this->getPaginator()->hasPages())
                <x-filament::pagination
                    :paginator="$this->getPaginator()"
                    :page-options="[]"
                />
            @endif

            {{-- Save --}}
            <div class="flex items-center justify-between mt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Klik simpan untuk menyimpan nilai semua halaman sekaligus.
                </p>
                <x-filament::button
                    type="submit"
                    color="success"
                    icon="heroicon-m-check-circle"
                    size="lg"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">Simpan Semua Nilai</span>
                    <span wire:loading wire:target="save">Menyimpan…</span>
                </x-filament::button>
            </div>
        @endif
    </form>
</x-filament-panels::page>
