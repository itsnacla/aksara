<?php

namespace App\Filament\Resources\SchoolSettings\Pages;

use App\Filament\Resources\SchoolSettings\SchoolSettingResource;
use App\Models\SchoolSetting;
use Filament\Resources\Pages\EditRecord;

class ManageSchoolSetting extends EditRecord
{
    protected static string $resource = SchoolSettingResource::class;

    public function mount($record = null): void
    {
        $record = SchoolSetting::first() ?? SchoolSetting::create([
            'name' => 'Nama Sekolah Baru',
        ]);

        parent::mount($record->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
