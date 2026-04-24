<?php

namespace Tests\Feature\Administrations;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_can_create_an_administration(): void
    {
        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $createdBy = User::factory()->create();
        $nurse = User::factory()->nurse()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->actingAs($nurse)->postJson('/api/administrations', $payload);

        $response->assertCreated();

        $response->assertJsonPath('data.user.id', $nurse->id);
        $response->assertJsonPath('data.user.name', $nurse->name);

        $response->assertJsonPath('data.prescription.id', $prescription->id);
        $response->assertJsonPath('data.prescription.dosage', $prescription->dosage);
        $response->assertJsonPath('data.prescription.frequency', $prescription->frequency);
        $response->assertJsonPath('data.prescription.status', $prescription->status);

        $response->assertJsonPath('data.status', 'given');

        $this->assertDatabaseHas('administrations', [
            'prescription_id' => $prescription->id,
            'user_id' => $nurse->id,
            'status' => 'given',
        ]);
    }

    public function test_doctor_cannot_create_an_administration(): void
    {
        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $createdBy = User::factory()->create();
        $doctor = User::factory()->doctor()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->actingAs($doctor)->postJson('/api/administrations', $payload);

        $response->assertForbidden();

        $this->assertDatabaseMissing('administrations', [
            'prescription_id' => $prescription->id,
            'user_id' => $doctor->id,
            'status' => 'given',
        ]);
    }

}
