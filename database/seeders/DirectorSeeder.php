<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Director;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DirectorSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('DIRECTOR_EMAIL', 'director@gmail.com');
        $password = env('DIRECTOR_PASSWORD', 'password1234');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'John Doe',
                'password' => Hash::make($password),
                'role' => UserRole::Director,
                'is_active' => true,
            ]
        );

        Director::updateOrCreate(['user_id' => $user->id]);
    }
}
