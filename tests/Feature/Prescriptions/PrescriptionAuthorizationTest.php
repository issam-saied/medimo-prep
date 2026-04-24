<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_create_a_prescription(): void
    {
        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $doctor = User::factory()->doctor()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->actingAs($doctor)->postJson('/api/prescriptions', $payload);

        $response->assertCreated();

        $response->assertJsonPath('data.patient.id', $patient->id);
        $response->assertJsonPath('data.medication.id', $medication->id);
        $response->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $doctor->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
        ]);
    }

    public function test_nurse_cannot_create_a_prescription(): void
    {
        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $nurse = User::factory()->nurse()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->actingAs($nurse)->postJson('/api/prescriptions', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'dosage' => '500 mg',
            'frequency' => 3,
        ]);
    }

    public function test_guest_cannot_create_a_prescription(): void
    {
        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->postJson('/api/prescriptions', $payload);

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('prescriptions', [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'dosage' => '500 mg',
            'frequency' => 3,
        ]);
    }
}
