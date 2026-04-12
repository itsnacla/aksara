<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

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

        // Auto-fill nama_siswa from user_name
        $data['nama_siswa'] = $data['user_name'];

        // Remove user fields from data, keep only student fields
        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

        // Create User account
        $user = User::create([
            'name' => $this->userData['name'],
            'username' => $this->userData['username'],
            'email' => $this->userData['email'],
            'password' => Hash::make($this->userData['password']),
        ]);

        // Assign role
        $user->syncRoles(['siswa']);

        // Set user_id on student data
        $data['user_id'] = $user->id;

        return $data;
    }
}
