<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gmail.com')],
            [
                'name' => 'Admin',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password1234')),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
