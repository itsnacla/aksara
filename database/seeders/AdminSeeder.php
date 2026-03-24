<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Admin',
            'username'  => 'admin',
            'email'     => 'admin@aksara.com',
            'password'  => Hash::make('admin123'),
            'role_user' => 'admin',
            'created_at'=> Carbon::now(),
        ]);
    }
}