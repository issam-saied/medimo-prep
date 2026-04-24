<?php

namespace Tests\Feature\Prescriptions;

use App\Events\AdministrationCreated;
use App\Events\PrescriptionCreated;
use App\Events\PrescriptionUpdated;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PrescriptionEventsTest extends TestCase
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

    public function test_prescription_created_event_is_dispatched(): void
    {
        // Authenticate as a doctor to create a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        Event::fake();

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $createdBy = User::factory()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $this->postJson('/api/prescriptions', $payload)->assertCreated();

        Event::assertDispatched(PrescriptionCreated::class);
    }

    public function test_prescription_update_event_is_dispatched(): void
    {
        // Authenticate as a doctor to update a prescription
        $user = User::factory()->doctor()->create();
        $this->auth($user);

        Event::fake();

        $patient = Patient::factory()->create();
        $medication = Medication::factory()->create();
        $prescriber = User::factory()->doctor()->create();
        $createdBy = User::factory()->create();

        $payload = [
            'patient_id' => $patient->id,
            'medication_id' => $medication->id,
            'prescriber_id' => $prescriber->id,
            'created_by_user_id' => $createdBy->id,
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => 3,
            'start_date' => now()->subDay()->toDateString(),
        ];

        $createResponse = $this->postJson('/api/prescriptions', $payload);
        $createResponse->assertCreated();

        $prescriptionId = $createResponse->json('data.id');

        $updatePayload = [
            'status' => 'completed',
            'dosage' => '400 mg',
            'frequency' => 1,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        $this->putJson('/api/prescriptions/' . $prescriptionId, $updatePayload)->assertOk();

        Event::assertDispatched(PrescriptionUpdated::class);
    }
}
