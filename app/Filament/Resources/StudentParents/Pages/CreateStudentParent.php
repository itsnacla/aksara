<?php

namespace App\Filament\Resources\StudentParents\Pages;

use App\Filament\Resources\StudentParents\StudentParentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudentParent extends CreateRecord
{
    protected static string $resource = StudentParentResource::class;

    protected array $userData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract user data
        $this->userData = [
            'name' => $data['user_name'],
            'username' => $data['user_username'],
            'email' => $data['user_email'],
            'password' => $data['user_password'],
        ];

        // Auto-fill nama_wali from user_name
        $data['nama_wali'] = $data['user_name'];

        // Remove user fields from data
        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

        // Create User account
        $user = User::create([
            'name' => $this->userData['name'],
            'username' => $this->userData['username'],
            'email' => $this->userData['email'],
            'password' => Hash::make($this->userData['password']),
        ]);

        // Assign role
        $user->syncRoles(['wali']);

        // Set user_id on parent data
        $data['user_id'] = $user->id;

        return $data;
    }
}
