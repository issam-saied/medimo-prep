<?php

namespace Tests\Feature\Administrations;

use App\Models\Administration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_an_administration(): void
    {
        $admin = User::factory()->admin()->create();
        $administration = Administration::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/administrations/{$administration->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('administrations', ['id' => $administration->id]);
    }

    public function test_nurse_cannot_delete_an_administration(): void
    {
        $nurse = User::factory()->nurse()->create();
        $administration = Administration::factory()->create();

        $response = $this->actingAs($nurse)->deleteJson("/api/administrations/{$administration->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('administrations', ['id' => $administration->id, 'deleted_at' => null]);
    }

    public function test_doctor_cannot_delete_an_administration(): void
    {
        $doctor = User::factory()->doctor()->create();
        $administration = Administration::factory()->create();

        $response = $this->actingAs($doctor)->deleteJson("/api/administrations/{$administration->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('administrations', ['id' => $administration->id, 'deleted_at' => null]);
    }

    public function test_cannot_delete_an_administration_that_does_not_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/administrations/999");

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_an_administration(): void
    {
        $administration = Administration::factory()->create();

        $response = $this->deleteJson("/api/administrations/{$administration->id}");

        $response->assertUnauthorized();
        $this->assertDatabaseHas('administrations', ['id' => $administration->id, 'deleted_at' => null]);
    }
}
