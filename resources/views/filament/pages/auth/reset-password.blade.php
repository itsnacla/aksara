<x-auth-layout subtitle="Masukkan kata sandi baru Anda">
    <form wire:submit="resetPassword">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg" class="w-full justify-center">
                Simpan Kata Sandi Baru
            </x-filament::button>
        </div>
    </form>
</x-auth-layout>
