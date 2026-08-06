<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AiAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ai@helpdesk.com'],
            [
                'name' => 'AI',
                'role' => 'agent',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
