<?php

namespace Tests\Feature\Administrations;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationActivityLogTest extends TestCase
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

    public function test_creating_an_administration_writes_an_activity_log(): void
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
            'user_id' => $this->user->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);
        $response->assertCreated();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => auth()->id(),
            'subject_id' => $response->json('data.id'),
            'action' => 'administration_created',
            'subject_type' => 'administration',
        ]);
    }
}
