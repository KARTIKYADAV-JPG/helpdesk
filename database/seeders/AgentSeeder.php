<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Seed a sample agent account.
     *
     * Idempotent — uses firstOrCreate so repeated runs never create duplicates.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name'              => 'Agent',
                'password'          => Hash::make('password123'),
                'role'              => 'agent',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Agent account ready: agent@example.com');
    }
}
