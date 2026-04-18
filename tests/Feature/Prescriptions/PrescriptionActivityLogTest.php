<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::factory()->create();
    }

    protected function auth(?User $user = null): static
    {
        $this->user = $user ?? User::factory()->create();

        return $this->actingAs($this->user);
    }

    public function test_creating_an_prescription_writes_an_activity_log(): void
    {
        // Authenticate as a doctor to create a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => '3x daily',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->postJson('/api/prescriptions', $payload);
        $response->assertCreated();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'subject_id' => $response->json('data.id'),
            'action' => 'prescription_created',
            'subject_type' => 'prescription',
        ]);
    }

    public function test_updating_a_prescription_writes_an_activity_log(): void
    {
        // Authenticate as a doctor to update a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => '3x daily',
            'start_date' => now()->subDay()->toDateString(),
        ];

        $createResponse = $this->postJson('/api/prescriptions', $payload);
        $createResponse->assertCreated();

        $prescriptionId = $createResponse->json('data.id');

        $updatePayload = [
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => '1x daily',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        $response = $this->putJson('/api/prescriptions/' . $prescriptionId, $updatePayload);
        $response->assertOk();

        $this->assertDatabaseCount('activity_logs', 2);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'subject_id' => $prescriptionId,
            'action' => 'prescription_updated',
            'subject_type' => 'prescription',
        ]);
    }
}
