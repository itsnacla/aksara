<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

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
            $data['user_is_active'] = $user->is_active;
            $data['user_photo'] = $user->photo;
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
            'is_active' => $data['user_is_active'] ?? true,
            'photo' => $data['user_photo'] ?? null,
        ];

        // Remove user fields from student data
        unset(
            $data['user_name'],
            $data['user_username'],
            $data['user_email'],
            $data['user_password'],
            $data['user_is_active'],
            $data['user_photo']
        );

        // Update connected user
        $user = $this->record->user;
        if ($user) {
            $updateData = array_filter([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'is_active' => $userData['is_active'],
                'photo' => $userData['photo'],
            ], fn ($value) => $value !== null);

            if (! empty($userData['password'])) {
                $updateData['password'] = Hash::make($userData['password']);
            }

            $user->update($updateData);
        }

        return $data;
    }
}
