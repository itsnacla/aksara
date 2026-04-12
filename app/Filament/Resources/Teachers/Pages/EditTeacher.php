<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        if ($user) {
            $data['user_name'] = $user->name;
            $data['user_username'] = $user->username;
            $data['user_email'] = $user->email;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract user data
        $userData = [
            'name' => $data['user_name'] ?? null,
            'username' => $data['user_username'] ?? null,
            'email' => $data['user_email'] ?? null,
            'password' => $data['user_password'] ?? null,
        ];

        // Auto-fill nama_guru from user_name
        $data['nama_guru'] = $data['user_name'] ?? $this->record->nama_guru;

        // Remove user fields from teacher data
        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

        // Update connected user
        $user = $this->record->user;
        if ($user) {
            $updateData = array_filter([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
            ]);

            if (!empty($userData['password'])) {
                $updateData['password'] = Hash::make($userData['password']);
            }

            $user->update($updateData);
        }

        return $data;
    }
}
