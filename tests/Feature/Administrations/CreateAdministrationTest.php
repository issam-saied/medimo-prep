<?php

namespace Tests\Feature\Administrations;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CreateAdministrationTest extends TestCase
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

    public function test_it_creates_an_administration_with_valid_data(): void
    {
        // Authenticate as a nurse to create an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();
        $createdBy = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);

        $response->assertCreated();

        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonPath('data.user.name', $user->name);

        $response->assertJsonPath('data.prescription.id', $prescription->id);
        $response->assertJsonPath('data.prescription.dosage', $prescription->dosage);
        $response->assertJsonPath('data.prescription.frequency', $prescription->frequency);
        $response->assertJsonPath('data.prescription.status', $prescription->status);

        $response->assertJsonPath('data.status', 'given');

        $this->assertDatabaseHas('administrations', [
            'prescription_id' => $prescription->id,
            'user_id' => $user->id,
            'status' => 'given',
        ]);
    }

    public function test_note_is_required_when_administration_is_missed(): void
    {
        // Authenticate as a nurse to create an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();
        $createdBy = User::factory()->create();
        $adminUser = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'user_id' => $adminUser->id,
            'status' => 'missed',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['note']);
    }

    public function test_note_is_required_when_administration_is_refused(): void
    {
        // Authenticate as a nurse to create an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();
        $createdBy = User::factory()->create();
        $adminUser = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'user_id' => $adminUser->id,
            'status' => 'refused',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['note']);
    }

    public function test_administration_must_belong_to_active_prescription(): void
    {
        // Authenticate as a nurse to create an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();
        $createdBy = User::factory()->create();
        $adminUser = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'completed',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'user_id' => $adminUser->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);

        $response->assertUnprocessable();
        //check AdministrationService to ensure that the validation error
        //is specifically for the prescription status
        $response->assertJsonValidationErrors(['prescription_id']);
    }

    public function test_administered_at_must_be_within_prescription_period(): void
    {
        // Authenticate as a nurse to create an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();
        $createdBy = User::factory()->create();
        $adminUser = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'user_id' => $adminUser->id,
            'status' => 'given',
            'administered_at' => now()->addDay(2)->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);

        $response->assertUnprocessable();
        // administered_at is outside the prescription period,
        // so we expect a validation error for administered_at
        $response->assertJsonValidationErrors(['administered_at']);
    }
}
