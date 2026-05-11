<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Hash;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Create User account
                    $user = User::create([
                        'name' => $data['user_name'],
                        'username' => $data['user_username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                    ]);

                    // Assign role
                    $user->syncRoles(['staff']);

                    // Set user_id on staff data
                    $data['user_id'] = $user->id;

                    // Remove user fields from data
                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

                    return $data;
                }),
        ];
    }
}
