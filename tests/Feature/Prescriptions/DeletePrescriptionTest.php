<?php

namespace Tests\Feature\Prescriptions;

use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletePrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_prescription(): void
    {
        $admin = User::factory()->admin()->create();
        $prescription = Prescription::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/prescriptions/{$prescription->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('prescriptions', ['id' => $prescription->id]);
    }

    public function test_doctor_cannot_delete_a_prescription(): void
    {
        $doctor = User::factory()->doctor()->create();
        $prescription = Prescription::factory()->create();

        $response = $this->actingAs($doctor)->deleteJson("/api/prescriptions/{$prescription->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'deleted_at' => null]);
    }

    public function test_nurse_cannot_delete_a_prescription(): void
    {
        $nurse = User::factory()->nurse()->create();
        $prescription = Prescription::factory()->create();

        $response = $this->actingAs($nurse)->deleteJson("/api/prescriptions/{$prescription->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'deleted_at' => null]);
    }

    public function test_cannot_delete_a_prescription_that_does_not_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/prescriptions/999");

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_a_prescription(): void
    {
        $prescription = Prescription::factory()->create();

        $response = $this->deleteJson("/api/prescriptions/{$prescription->id}");

        $response->assertUnauthorized();

        $this->assertDatabaseHas('prescriptions', ['id' => $prescription->id, 'deleted_at' => null]);
    }
}
