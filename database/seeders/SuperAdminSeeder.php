<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@aksara.com'],
            [
                'name' => 'Super Admin Aksara',
                'username' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('super_admin');
    }
}
