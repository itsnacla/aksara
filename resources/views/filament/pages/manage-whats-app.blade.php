<x-filament-panels::page>
    <x-filament::tabs label="WhatsApp Management" class="mt-4">
        <x-filament::tabs.item
            :active="$activeTab === 'settings'"
            wire:click="$set('activeTab', 'settings')"
            icon="heroicon-m-cog-6-tooth"
        >
            Pengaturan Gateway
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$activeTab === 'broadcast'"
            wire:click="$set('activeTab', 'broadcast')"
            icon="heroicon-m-megaphone"
        >
            Kirim Pengumuman
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="mt-6">
        @if ($activeTab === 'settings')
            <form wire:submit="save" class="space-y-6">
                {{ $this->settingsForm }}
                
                <div class="flex items-center gap-3 pt-8" style="margin-top: 32px !important;">
                    <x-filament::button type="submit" size="lg" class="px-6">
                        Simpan Pengaturan
                    </x-filament::button>
                </div>
            </form>
        @else
            <form wire:submit="sendBroadcast" class="space-y-6">
                {{ $this->broadcastForm }}

                <div class="flex items-center gap-3 pt-8" style="margin-top: 32px !important;">
                    <x-filament::button type="submit" color="success" size="lg" class="px-6">
                        Kirim Pengumuman Sekarang
                    </x-filament::button>
                </div>
            </form>
        @endif
    </div>
</x-filament-panels::page>
