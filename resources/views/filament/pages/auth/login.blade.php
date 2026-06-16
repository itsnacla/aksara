<x-auth-layout subtitle="Silakan masuk ke akun Anda">
    <form wire:submit="authenticate">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg" class="w-full justify-center">
                Masuk ke Sistem
            </x-filament::button>
        </div>
    </form>
</x-auth-layout>
