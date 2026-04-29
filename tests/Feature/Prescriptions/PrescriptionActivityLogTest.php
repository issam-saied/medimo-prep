<?php

namespace Tests\Feature\Prescriptions;

use App\Models\ActivityLog;
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
        $prescriber = User::factory()->doctor()->create();
        $nurse = User::factory()->nurse()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'nurse_id' => $nurse->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
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
        $prescriber = User::factory()->doctor()->create();
        $nurse = User::factory()->nurse()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'nurse_id' => $nurse->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateTimeString(),
        ];

        $createResponse = $this->postJson('/api/prescriptions', $payload);
        $createResponse->assertCreated();

        $prescriptionId = $createResponse->json('data.id');

        $updatePayload = [
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->toDateTimeString(),
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

    public function test_updating_a_prescription_changes_column_has_correct_from_to_structure(): void
    {
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $user->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateTimeString(),
        ]);

        $response = $this->putJson("/api/prescriptions/{$prescription->id}", [
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->toDateTimeString(),
        ]);

        $response->assertOk();

        $log = ActivityLog::where('action', 'prescription_updated')->first();
        $this->assertNotNull($log->changes);

        $this->assertArrayHasKey('dosage', $log->changes);
        $this->assertEquals('500 mg', $log->changes['dosage']['from']);
        $this->assertEquals('400 mg', $log->changes['dosage']['to']);

        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('active', $log->changes['status']['from']);
        $this->assertEquals('completed', $log->changes['status']['to']);

        $this->assertArrayHasKey('frequency', $log->changes);
        $this->assertEquals(3, $log->changes['frequency']['from']);
        $this->assertEquals(1, $log->changes['frequency']['to']);
    }
}
