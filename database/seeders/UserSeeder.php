<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['phone' => 'admin'],
            ['name' => 'Admin', 'phone' => 'admin', 'password' => Hash::make('admin123'), 'role' => 'admin', 'active' => true]
        );
        User::updateOrCreate(
            ['phone' => 'staff'],
            ['name' => 'Nhân viên', 'phone' => 'staff', 'password' => Hash::make('staff123'), 'role' => 'staff', 'active' => true]
        );
    }
}
