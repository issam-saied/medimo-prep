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
        $admins  = User::factory(1)->admin()->create();
        $doctors = User::factory(3)->doctor()->create();
        $nurses  = User::factory(3)->nurse()->create();

        $allUsers = $admins->merge($doctors)->merge($nurses);

        // patients
        $patients = Patient::factory(10)->create();

        // medications
        $medications = Medication::factory(5)->create();

        // prescriptions — prescriber must be a doctor, creator can be anyone
        $prescriptions = Prescription::factory(15)->create([
            'patient_id'          => fn () => $patients->random()->id,
            'medication_id'       => fn () => $medications->random()->id,
            'prescriber_id'       => fn () => $doctors->random()->id,
            'created_by_user_id'  => fn () => $allUsers->random()->id,
        ]);

        // administrations — administered by nurses
        Administration::factory(30)->create([
            'prescription_id' => fn () => $prescriptions->random()->id,
            'user_id'         => fn () => $nurses->random()->id,
        ]);
    }
}
