<?php

namespace App\Filament\Resources\StudentLeaves\Pages;

use App\Filament\Resources\StudentLeaves\StudentLeaveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudentLeaves extends ManageRecords
{
    protected static string $resource = StudentLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $student = \App\Models\Student::find($data['student_id']);
                    if ($student) {
                        $data['parent_id'] = $student->parent_id;
                        $data['study_group_id'] = $student->currentStudyGroup()?->id;
                    }
                    if ($data['status'] === 'approved') {
                        $data['approved_by'] = auth()->id();
                    }
                    return $data;
                })
                ->after(function (\App\Models\StudentLeave $record) {
                    if ($record->status === 'approved') {
                        StudentLeaveResource::syncToAttendance($record);
                    }
                }),
        ];
    }
}
