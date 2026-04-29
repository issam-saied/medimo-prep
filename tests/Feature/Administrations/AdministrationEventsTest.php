<?php

namespace Tests\Feature\Administrations;

use App\Events\AdministrationCreated;
use App\Events\AdministrationUpdated;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdministrationEventsTest extends TestCase
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

    public function test_administration_created_event_is_dispatched(): void
    {
        // Authenticate as a nurse to create an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        Event::fake();

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $createdBy = User::factory()->create();
        $administrationUser = User::factory()->create();

        $prescription = Prescription::factory()->create([
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'nurse_id' => $user->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'user_id' => $administrationUser->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $this->postJson('/api/administrations', $payload)->assertCreated();

        Event::assertDispatched(AdministrationCreated::class);
    }

    public function test_administration_updated_event_is_dispatched(): void
    {
        // Authenticate as a nurse to update an administration
        // Create a prescription directly in the database as test setup to ensure it exists for the administration
        $user = User::factory()->nurse()->create();
        $this->auth($user);

        Event::fake();

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
            'nurse_id' => $user->id,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
        ]);

        $payload = [
            'prescription_id' => $prescription->id,
            'user_id' => $administrationUser->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ];

        $response = $this->postJson('/api/administrations', $payload);

        $updatePayload = [
            'note' => 'Updated note',
        ];

        $response = $this->putJson('/api/administrations/' . $response->json('data.id'), $updatePayload);

        $response->assertOk();

        Event::assertDispatched(AdministrationUpdated::class);
    }
}
