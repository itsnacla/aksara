<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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

        // Auto-fill nama_staff from user_name
        $data['nama_staff'] = $data['user_name'] ?? $this->record->nama_staff;

        // Remove user fields from staff data
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
