<?php

namespace Tests\Feature\Administrations;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAdministrationTest extends TestCase
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

    public function test_only_note_can_be_updated_for_administration(): void
    {
        // Authenticate as a nurse to update an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->create();
        $createdBy = User::factory()->create();
        $administrationUser = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $createPayload = [
            'prescription_id' => $prescription->id,
            'user_id' => $administrationUser->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => 'Initial note',
        ];

        $createResponse = $this->postJson('/api/administrations', $createPayload);
        $createResponse->assertCreated();

        $administrationId = $createResponse->json('data.id');
        $originalStatus =  $createResponse->json('data.status');
        $originalAdministeredAt =  $createResponse->json('data.administered_at');

        $updatePayload = [
            'status' => 'refused',
            'administered_at' => now()->addDay()->toDateTimeString(),
            'note' => 'Updated note',
        ];

        $response = $this->putJson('/api/administrations/' . $administrationId, $updatePayload);

        $response->assertOk();

        $this->assertDatabaseHas('administrations', [
            'id' => $administrationId,
            'note' => 'Updated note',
            'status' => 'given',
            'administered_at' => $originalAdministeredAt,
        ]);

        $this->assertSame('Updated note', $response->json('data.note'));
        $this->assertSame($originalStatus, $response->json('data.status'));
        $this->assertEquals($originalAdministeredAt, $response->json('data.administered_at'));
    }
}
