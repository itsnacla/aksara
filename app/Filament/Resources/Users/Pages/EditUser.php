<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Teacher;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record;
        $roleName = $user->roles->first()?->name;

        $data['selected_role'] = $roleName;

        match ($roleName) {
            'guru' => $this->fillTeacherData($data, $user),
            'staff' => $this->fillStaffData($data, $user),
            'siswa' => $this->fillStudentData($data, $user),
            'wali' => $this->fillParentData($data, $user),
            default => null,
        };

        return $data;
    }

    private function fillTeacherData(array &$data, $user): void
    {
        $teacher = $user->teacher;
        if ($teacher) {
            $data['teacher_nip'] = $teacher->nip;
            $data['teacher_spesialisasi'] = $teacher->spesialisasi;
            $data['teacher_no_whatsapp'] = $teacher->no_whatsapp;
            $data['teacher_is_walikelas'] = $teacher->is_walikelas;
            $data['teacher_is_kepalasekolah'] = $teacher->is_kepalasekolah;
        }
    }

    private function fillStaffData(array &$data, $user): void
    {
        $staff = $user->staff;
        if ($staff) {
            $data['staff_jabatan'] = $staff->jabatan;
            $data['staff_no_whatsapp'] = $staff->no_whatsapp;
        }
    }

    private function fillStudentData(array &$data, $user): void
    {
        $student = $user->student;
        if ($student) {
            $data['student_nisn'] = $student->nisn;
            $data['student_classroom_id'] = $student->classroom_id;
            $data['student_parent_id'] = $student->parent_id;
        }
    }

    private function fillParentData(array &$data, $user): void
    {
        $parent = $user->parent;
        if ($parent) {
            $data['parent_hubungan'] = $parent->hubungan;
            $data['parent_no_whatsapp'] = $parent->no_whatsapp;
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleSpecificData = $data;

        return collect($data)->only([
            'name', 'username', 'email', 'password',
        ])->filter()->toArray();
    }

    protected array $roleSpecificData = [];

    protected function afterSave(): void
    {
        $user = $this->record;
        $data = $this->roleSpecificData;
        $newRole = $data['selected_role'] ?? null;
        $oldRole = $user->roles->first()?->name;

        if (!$newRole) {
            return;
        }

        if ($oldRole && $oldRole !== $newRole) {
            match ($oldRole) {
                'guru' => $user->teacher?->delete(),
                'staff' => $user->staff?->delete(),
                'siswa' => $user->student?->delete(),
                'wali' => $user->parent?->delete(),
                default => null,
            };
        }

        $user->syncRoles([$newRole]);

        match ($newRole) {
            'guru' => Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $data['teacher_nip'] ?? '',
                    'spesialisasi' => $data['teacher_spesialisasi'] ?? null,
                    'no_whatsapp' => $data['teacher_no_whatsapp'] ?? null,
                    'is_walikelas' => $data['teacher_is_walikelas'] ?? false,
                    'is_kepalasekolah' => $data['teacher_is_kepalasekolah'] ?? false,
                ]
            ),
            'staff' => Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'jabatan' => $data['staff_jabatan'] ?? null,
                    'no_whatsapp' => $data['staff_no_whatsapp'] ?? null,
                ]
            ),
            'siswa' => Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nisn' => $data['student_nisn'] ?? '',
                    'classroom_id' => $data['student_classroom_id'],
                    'parent_id' => $data['student_parent_id'],
                ]
            ),
            'wali' => StudentParent::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'hubungan' => $data['parent_hubungan'] ?? 'wali',
                    'no_whatsapp' => $data['parent_no_whatsapp'] ?? null,
                ]
            ),
            default => null,
        };
    }
}
