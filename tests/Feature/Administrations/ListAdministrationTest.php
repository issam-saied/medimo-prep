<?php

namespace Tests\Feature\Administrations;

use App\Models\Administration;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ListAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    protected function auth(): static
    {
        return $this->actingAs($this->user);
    }

    public function test_it_can_filter_administrations_by_status(): void
    {
        $this->auth();

        // Arrange: create data
        $prescription = Prescription::factory()->create([
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => '1x daily',
        ]);

        Administration::factory()->create([
            'prescription_id' => $prescription->id,
            'user_id' => $this->user->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ]);

        Administration::factory()->create([
            'prescription_id' => $prescription->id,
            'user_id' => $this->user->id,
            'status' => 'refused',
            'administered_at' => now()->toDateTimeString(),
            'note' => 'Patient refused medication',
        ]);

        // Act
        $response = $this->getJson('/api/administrations?status=given');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        // Assert: only active prescription are present
        $response->assertJsonFragment([
            'status' => 'given',
        ]);

        // Assert: completed are not present
        $response->assertJsonMissing([
            'status' => 'refused',
        ]);
    }

    public function test_it_can_filter_administrations_by_patient(): void
    {
        $this->auth();

        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        $prescription1 = Prescription::factory()->create([
            'patient_id' => $patient1->id,
            'status' => 'active',
            'dosage' => '400 mg',
            'frequency' => '3x daily',
        ]);

        $prescription2 = Prescription::factory()->create([
            'patient_id' => $patient2->id,
            'status' => 'stopped',
            'dosage' => '500 mg',
            'frequency' => '1x daily',
        ]);

        Administration::factory()->create([
            'prescription_id' => $prescription1->id,
            'user_id' => $this->user->id,
            'status' => 'given',
            'administered_at' => now()->toDateTimeString(),
            'note' => null,
        ]);

        Administration::factory()->create([
            'prescription_id' => $prescription2->id,
            'user_id' => $this->user->id,
            'status' => 'refused',
            'administered_at' => now()->toDateTimeString(),
            'note' => 'Patient refused medication',
        ]);

        $response = $this->getJson('/api/administrations?patient_id=' . $patient1->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'status' => 'given',
            'dosage' => $prescription1->dosage,
            'frequency' => $prescription1->frequency,
        ]);

        $response->assertJsonMissing([
            'status' => 'refused',
            'dosage' => $prescription2->dosage,
            'frequency' => $prescription2->frequency,
        ]);
    }

}
