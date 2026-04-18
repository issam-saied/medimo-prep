<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ListPrescriptionTest extends TestCase
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

    public function test_it_can_filter_prescriptions_by_status(): void
    {
        $this->auth();

        // Arrange: create data
        $activePrescription = Prescription::factory()->create([
            'status' => 'active',
            'dosage' => '500 mg',
            'frequency' => '1x daily',
        ]);

        $completedPrescription = Prescription::factory()->create([
            'status' => 'completed',
            'dosage' => '999 mg',
            'frequency' => '9x daily',
        ]);

        // Act
        $response = $this->getJson('/api/prescriptions?status=active');

        $response->assertOk();

        // Assert: only active prescription are present
        $response->assertJsonFragment([
            'status' => 'active',
            'dosage' => '500 mg',
        ]);

        // Assert: completed are not present
        $response->assertJsonMissing([
            'status' => 'completed',
            'dosage' => '999 mg',
        ]);
    }

}
