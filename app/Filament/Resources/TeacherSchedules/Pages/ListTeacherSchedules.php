<?php

namespace App\Filament\Resources\TeacherSchedules\Pages;

use App\Filament\Resources\TeacherSchedules\TeacherScheduleResource;
use Filament\Resources\Pages\ListRecords;

class ListTeacherSchedules extends ListRecords
{
    protected static string $resource = TeacherScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
