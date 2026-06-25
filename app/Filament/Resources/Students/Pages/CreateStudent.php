<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\StudentParent;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected array $userData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Handle Parent Creation if requested
        if (isset($data['create_new_parent']) && $data['create_new_parent']) {
            $parentUser = User::create([
                'name' => $data['parent_name'],
                'username' => $data['parent_username'],
                'email' => $data['parent_email'],
                'password' => Hash::make($data['parent_password']),
            ]);

            $parentUser->syncRoles(['wali']);

            $studentParent = StudentParent::create([
                'user_id' => $parentUser->id,
                'no_whatsapp' => $data['parent_whatsapp'] ?? null,
                'hubungan' => $data['parent_relation'],
            ]);

            $data['parent_id'] = $studentParent->id;
        }

        // Clean up parent fields from student data
        unset(
            $data['create_new_parent'],
            $data['parent_name'],
            $data['parent_username'],
            $data['parent_email'],
            $data['parent_password'],
            $data['parent_relation'],
            $data['parent_whatsapp']
        );

        // 2. Handle Student User Creation
        $this->userData = [
            'name' => $data['user_name'],
            'username' => $data['user_username'],
            'email' => $data['user_email'],
            'password' => $data['user_password'],
            'is_active' => $data['user_is_active'] ?? true,
            'photo' => $data['user_photo'] ?? null,
        ];

        // Remove user fields from data, keep only student fields
        unset(
            $data['user_name'],
            $data['user_username'],
            $data['user_email'],
            $data['user_password'],
            $data['user_is_active'],
            $data['user_photo']
        );

        // Create User account
        $user = User::create([
            'name' => $this->userData['name'],
            'username' => $this->userData['username'],
            'email' => $this->userData['email'],
            'password' => Hash::make($this->userData['password']),
            'is_active' => $this->userData['is_active'],
            'photo' => $this->userData['photo'],
        ]);

        // Assign role
        $user->syncRoles(['siswa']);

        // Set user_id on student data
        $data['user_id'] = $user->id;

        return $data;
    }
}
