<?php

namespace Tests\Feature\Medications;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteMedicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_medication(): void
    {
        $admin = User::factory()->admin()->create();
        $medication = Medication::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/medications/{$medication->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('medications', ['id' => $medication->id]);
    }

    public function test_doctor_cannot_delete_a_medication(): void
    {
        $doctor = User::factory()->doctor()->create();
        $medication = Medication::factory()->create();

        $response = $this->actingAs($doctor)->deleteJson("/api/medications/{$medication->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('medications', ['id' => $medication->id, 'deleted_at' => null]);
    }

    public function test_nurse_cannot_delete_a_medication(): void
    {
        $nurse = User::factory()->nurse()->create();
        $medication = Medication::factory()->create();

        $response = $this->actingAs($nurse)->deleteJson("/api/medications/{$medication->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('medications', ['id' => $medication->id, 'deleted_at' => null]);
    }

    public function test_cannot_delete_a_medication_that_does_not_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/medications/999");

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_a_medication(): void
    {
        $medication = Medication::factory()->create();

        $response = $this->deleteJson("/api/medications/{$medication->id}");

        $response->assertUnauthorized();
        $this->assertDatabaseHas('medications', ['id' => $medication->id, 'deleted_at' => null]);
    }
}
