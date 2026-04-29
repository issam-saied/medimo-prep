<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class UpdatePrescriptionTest extends TestCase
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

    public function test_end_date_is_required_when_status_is_completed_on_update(): void
    {
        // Authenticate as a doctor to create a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $createdBy = User::factory()->create();

        $nurse = User::factory()->nurse()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'nurse_id' => $nurse->id,
            'created_by_user_id' => $createdBy->id,
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
        ];

        $response = $this->putJson('/api/prescriptions/' . $prescriptionId, $updatePayload);
        // Assert that the response has a 422 Unprocessable Entity status
        $response->assertUnprocessable();

        // Verify that the validation error for 'end_date' is present
        $response->assertJsonValidationErrors(['end_date']);

        // Verify that the prescription status has not been updated to 'completed'
        $this->assertDatabaseHas('prescriptions', [
            'id' =>  $prescriptionId,
            'status' => 'active',
        ]);
    }

    public function test_it_updates_a_prescription_with_valid_data(): void
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
        // Assert that the response has a 200 OK status
        $response->assertOk();

        $response->assertJsonPath('data.status', 'completed');
        $response->assertJsonPath('data.dosage', '400 mg');
        $response->assertJsonPath('data.frequency', 1);
        $response->assertJsonPath('data.start_date', $updatePayload['start_date']);
        $response->assertJsonPath('data.end_date', $updatePayload['end_date']);

        $response->assertJsonPath('data.patient.id', $patient->id);
        $response->assertJsonPath('data.medication.id', $medication->id);
        $response->assertJsonPath('data.prescriber.id', $prescriber->id);
        $response->assertJsonPath('data.createdByUser.id', auth()->id());

        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescriptionId,
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => auth()->id(),
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
            'start_date' => $updatePayload['start_date'],
            'end_date' => $updatePayload['end_date'],
        ]);
    }

    public function test_medication_id_cannot_be_updated_for_a_prescription(): void
    {
        // Authenticate as a doctor to create a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $newMedication = Medication::factory()->create();
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

        $createResponse = $this->postJson('/api/prescriptions', $payload);

        $createResponse->assertCreated();

        $prescriptionId = $createResponse->json('data.id');

        $updatePayload = [
            'medication_id' => $newMedication->id,
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
        ];

        $response = $this->putJson('/api/prescriptions/' . $prescriptionId, $updatePayload);
        // Assert that the response has a 200 OK status
        $response->assertOk();

        $response->assertJsonPath('data.medication.id', $medication->id);
        $response->assertJsonPath('data.status', 'completed');
        $response->assertJsonPath('data.dosage', '400 mg');
        $response->assertJsonPath('data.frequency', 1);

        // Verify that the prescription has been updated with the new status, dosage, and frequency
        // but the medication_id remains unchanged
        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescriptionId,
            'medication_id' => $medication->id,
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
        ]);

        // Verify that the medication_id has not been updated to the new medication
        $this->assertDatabaseMissing('prescriptions', [
            'id' => $prescriptionId,
            'medication_id' => $newMedication->id,
        ]);
    }

    public function test_patient_id_cannot_be_updated_for_a_prescription(): void
    {
        // Authenticate as a doctor to create a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $newPatient = Patient::factory()->create();
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

        $createResponse = $this->postJson('/api/prescriptions', $payload);
        $createResponse->assertCreated();

        $prescriptionId = $createResponse->json('data.id');

        $updatePayload = [
            'patient_id' => $newPatient->id,
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
        ];

        $response = $this->putJson('/api/prescriptions/' . $prescriptionId, $updatePayload);
        // Assert that the response has a 200 OK status
        $response->assertOk();

        $response->assertJsonPath('data.medication.id', $medication->id);
        $response->assertJsonPath('data.status', 'completed');
        $response->assertJsonPath('data.dosage', '400 mg');
        $response->assertJsonPath('data.frequency', 1);

        // Verify that the prescription has been updated with the new status, dosage, and frequency
        // but the patient_id remains unchanged
        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescriptionId,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
        ]);

        // Verify that the patient_id has not been updated to the new patient
        $this->assertDatabaseMissing('prescriptions', [
            'id' => $prescriptionId,
            'patient_id' => $newPatient->id,
        ]);
    }
}
