<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;
    protected function afterCreate(): void
    {
        $attendance = $this->record;
        
        if (!$attendance->wa_sent_at) {
            \App\Jobs\SendWhatsAppAttendanceNotification::dispatch($attendance);
        }
    }
}
