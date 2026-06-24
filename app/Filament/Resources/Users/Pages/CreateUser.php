<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Teacher;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleSpecificData = $data;

        return collect($data)->only([
            'name', 'username', 'email', 'password',
        ])->toArray();
    }

    protected array $roleSpecificData = [];

    protected function afterCreate(): void
    {
        $user = $this->record;
        $data = $this->roleSpecificData;
        $role = $data['selected_role'] ?? null;

        if (!$role) {
            return;
        }

        $user->syncRoles([$role]);

        match ($role) {
            'guru' => Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['teacher_nip'] ?? '',
                'spesialisasi' => $data['teacher_spesialisasi'] ?? null,
                'no_whatsapp' => $data['teacher_no_whatsapp'] ?? null,
                'is_walikelas' => $data['teacher_is_walikelas'] ?? false,
                'is_kepalasekolah' => $data['teacher_is_kepalasekolah'] ?? false,
            ]),
            'staff' => Staff::create([
                'user_id' => $user->id,
                'jabatan' => $data['staff_jabatan'] ?? null,
                'no_whatsapp' => $data['staff_no_whatsapp'] ?? null,
            ]),
            'siswa' => Student::create([
                'user_id' => $user->id,
                'nisn' => $data['student_nisn'] ?? '',
                'classroom_id' => $data['student_classroom_id'],
                'parent_id' => $data['student_parent_id'],
            ]),
            'wali' => StudentParent::create([
                'user_id' => $user->id,
                'hubungan' => $data['parent_hubungan'] ?? 'wali',
                'no_whatsapp' => $data['parent_no_whatsapp'] ?? null,
            ]),
            default => null,
        };
    }
}
