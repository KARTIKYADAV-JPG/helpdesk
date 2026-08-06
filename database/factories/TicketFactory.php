<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(fake()->numberBetween(4, 8)),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(TicketCategory::values()),
            'status' => fake()->randomElement(TicketStatus::values()),
            'priority' => fake()->randomElement(TicketPriority::values()),
            'created_by' => User::factory(),
            'assigned_to' => fake()->boolean(70) ? User::factory()->state(['role' => 'agent']) : null,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
