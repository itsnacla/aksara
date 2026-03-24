<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StudentParent;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->role_user === 'staff') {
            Staff::create([
                'user_id' => $user->id,
                'nama_staff' => $user->name,
            ]);
        }

        if ($user->role_user === 'guru') {
            Teacher::create([
                'user_id' => $user->id,
            ]);
        }

        if ($user->role_user === 'siswa') {
            Student::create([
                'user_id' => $user->id,
            ]);
        }

        if ($user->role_user === 'wali') {
            StudentParent::create([
                'user_id' => $user->id,
            ]);
        }
    }
}
