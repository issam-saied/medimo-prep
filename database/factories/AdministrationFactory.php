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
        $status = fake()->randomElement(['given', 'missed', 'refused']);

        return [
            'prescription_id' => \App\Models\Prescription::factory(),
            'user_id' => \App\Models\User::factory(),
            'status' => $status,
            'administered_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'note' => in_array($status, ['missed', 'refused']) ? fake()->sentence() : fake()->optional()->sentence(),
        ];
    }
}
