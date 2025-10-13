<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{

    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@gmail.com');
        $password = env('ADMIN_PASSWORD', 'password1234');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'System Admin',
                'password' => Hash::make($password),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );
    }
}
