<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the default administrator account.
     *
     * This seeder is idempotent — it uses firstOrCreate so running
     * db:seed multiple times will never create duplicate admin accounts.
     */
    public function run(): void
    {
        User::firstOrCreate(
            // Lookup condition — match by email
            ['email' => 'admin@helpdesk.com'],
            // Values to set when creating
            [
                'name'              => 'Admin',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin account ready: admin@helpdesk.com');
    }
}
