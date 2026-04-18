<?php

namespace Database\Factories;

use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-5 days', 'now');
        $end = (clone $start)->modify('+7 days');

        return [
            'patient_id' => \App\Models\Patient::factory(),
            'medication_id' => \App\Models\Medication::factory(),
            'prescriber_id' => \App\Models\User::factory(),
            'created_by_user_id' => \App\Models\User::factory(),
            'dosage' => fake()->randomElement(['500 mg', '250 mg']),
            'frequency' => fake()->randomElement(['1x daily', '3x daily']),
            'status' => 'active',
            'start_date' => $start,
            'end_date' => $end,
        ];
    }
}
