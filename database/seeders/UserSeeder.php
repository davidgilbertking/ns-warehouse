<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
                         'name' => 'Admin',
                         'email' => 'admin@local',
                         'password' => Hash::make('password'),
                         'role' => 'admin',
                     ]);

        User::create([
                         'name' => 'User',
                         'email' => 'user@local',
                         'password' => Hash::make('password'),
                         'role' => 'user',
                     ]);

        User::create([
                         'name' => 'Viewer',
                         'email' => 'viewer@local',
                         'password' => Hash::make('password'),
                         'role' => 'viewer',
                     ]);
    }
}
