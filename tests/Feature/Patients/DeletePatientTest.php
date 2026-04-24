<?php

namespace Tests\Feature\Patients;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletePatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_patient(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = Patient::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/patients/{$patient->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }

    public function test_doctor_cannot_delete_a_patient(): void
    {
        $doctor = User::factory()->doctor()->create();
        $patient = Patient::factory()->create();

        $response = $this->actingAs($doctor)->deleteJson("/api/patients/{$patient->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'deleted_at' => null]);
    }

    public function test_nurse_cannot_delete_a_patient(): void
    {
        $nurse = User::factory()->nurse()->create();
        $patient = Patient::factory()->create();

        $response = $this->actingAs($nurse)->deleteJson("/api/patients/{$patient->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'deleted_at' => null]);
    }

    public function test_cannot_delete_a_patient_that_does_not_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/patients/999");

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_a_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->deleteJson("/api/patients/{$patient->id}");

        $response->assertUnauthorized();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'deleted_at' => null]);
    }
}
