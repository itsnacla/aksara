<?php

namespace App\Filament\Resources\StudentParents\Pages;

use App\Filament\Resources\StudentParents\StudentParentResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListStudentParents extends ListRecords
{
    protected static string $resource = StudentParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('7xl')
                ->mutateFormDataUsing(function (array $data): array {
                    // Create User account
                    $user = User::create([
                        'name' => $data['user_name'],
                        'username' => $data['user_username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                    ]);

                    // Assign role
                    $user->syncRoles(['wali']);

                    // Set user_id on parent data
                    $data['user_id'] = $user->id;

                    // Remove user fields from data
                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

                    return $data;
                }),
        ];
    }
}
