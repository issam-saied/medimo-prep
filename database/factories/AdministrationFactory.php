<?php

namespace Database\Factories;

use App\Models\Administration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Administration>
 */
class AdministrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prescription_id' => \App\Models\Prescription::factory(),
            'user_id' => \App\Models\User::factory(),
            'status' => fake()->randomElement(['given', 'missed', 'refused']),
            'administered_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
