<?php

namespace Database\Seeders;

use App\Models\Administration;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // users
        $users = User::factory(5)->create();

        // patients
        $patients = Patient::factory(10)->create();

        // medications
        $medications = Medication::factory(5)->create();

        // prescriptions
        $prescriptions = Prescription::factory(15)->create([
            'patient_id' => fn () => $patients->random()->id,
            'medication_id' => fn () => $medications->random()->id,
            'prescriber_id' => fn () => $users->random()->id,
            'created_by_user_id' => fn () => $users->random()->id,
        ]);

        // administrations
        Administration::factory(30)->create([
            'prescription_id' => fn () => $prescriptions->random()->id,
            'user_id' => fn () => $users->random()->id,
        ]);
    }
}
