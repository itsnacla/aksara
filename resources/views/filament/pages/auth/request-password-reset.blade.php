<x-auth-layout subtitle="Masukkan email Anda untuk mereset kata sandi">
    <form wire:submit="request">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg" class="w-full justify-center">
                Kirim Tautan Reset
            </x-filament::button>
        </div>
        
        <div class="mt-4 text-center">
            <a href="{{ filament()->getLoginUrl() }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                &larr; Kembali ke halaman login
            </a>
        </div>
    </form>
</x-auth-layout>
