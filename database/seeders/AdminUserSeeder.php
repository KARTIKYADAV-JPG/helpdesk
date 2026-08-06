<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('admin.name', 'Admin');
        $email = config('admin.email');
        $password = config('admin.password');

        if (empty($email) || empty($password)) {
            $this->command->error('ADMIN_EMAIL and ADMIN_PASSWORD must be configured in .env');
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin user '{$name}' ({$email}) seeded/updated successfully.");
    }
}
