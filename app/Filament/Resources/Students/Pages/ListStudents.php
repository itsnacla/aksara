<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use App\Models\StudentParent;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Hash;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
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
                    $user = User::create([
                        'name' => $data['user_name'],
                        'username' => $data['user_username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                    ]);

                    // Assign role
                    $user->syncRoles(['siswa']);

                    // Set user_id on student data
                    $data['user_id'] = $user->id;

                    // Remove user fields from data, keep only student fields
                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

                    return $data;
                }),
        ];
    }
}
