<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CreatePrescriptionTest extends TestCase
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

    public function test_it_creates_a_prescription_with_valid_data(): void
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

        $response->assertJsonStructure([
            'data' => [
                'id',
                'patient' => [
                    'id',
                    'name',
                    'birthdate'
                ],
                'medication' => [
                    'id',
                    'name',
                    'strength',
                    'unit'
                ],
                'prescriber' => [
                    'id',
                    'name',
                    'job_title',
                    'organization'
                ],
                'createdByUser' => [
                    'id',
                    'name',
                    'job_title',
                    'organization',
                ],
                'dosage',
                'frequency',
                'status',
                'start_date',
                'end_date',
            ],
        ]);

        $response->assertJsonPath('data.patient.id', $patient->id);
        $response->assertJsonPath('data.patient.name', $patient->name);

        $response->assertJsonPath('data.medication.id', $medication->id);
        $response->assertJsonPath('data.medication.name', $medication->name);

        $response->assertJsonPath('data.prescriber.id', $prescriber->id);
        $response->assertJsonPath('data.prescriber.name', $prescriber->name);

        $response->assertJsonPath('data.createdByUser.id', auth()->id());
        $response->assertJsonPath('data.createdByUser.name',  auth()->user()->name);

        $response->assertJsonPath('data.dosage', '500 mg');
        $response->assertJsonPath('data.frequency', 3);
        $response->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => auth()->id(),
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
        ]);
    }

    public function test_it_prevents_creating_a_second_active_prescription_for_the_same_patient_and_medication(): void
    {
        // Authenticate as a doctor to create a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $nurse = User::factory()->nurse()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'nurse_id' => $nurse->id,
            'status' => 'active',
            'dosage' => '300 mg',
            'frequency' => 1,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/prescriptions', $payload);

        $response->assertUnprocessable();

        //Simple version
        $response->assertJsonValidationErrors(['medication_id']);
        // OR
        $response->assertJsonFragment([
            'medication_id' => [
                'This patient already has an active prescription for this medication.'
            ]
        ]);

        // make sure here is only 1 prescription in the database for this combination.
        $this->assertEquals(1, \App\Models\Prescription::query()
            ->where('patient_id', $patient->id)
            ->where('medication_id', $medication->id)
            ->where('status', 'active')
            ->count());
    }

}
