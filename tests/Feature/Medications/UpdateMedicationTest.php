<?php

namespace Tests\Feature\Medications;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMedicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_a_medication(): void
    {
        $user = User::factory()->create();
        $medication = Medication::factory()->create(['name' => 'Ibuprofen', 'strength' => '200']);

        $response = $this->actingAs($user)->putJson("/api/medications/{$medication->id}", [
            'name' => 'Ibuprofen Updated',
            'strength' => '400',
            'unit' => 'mg',
            'form' => 'tablet',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('medications', ['id' => $medication->id, 'name' => 'Ibuprofen Updated', 'strength' => '400']);
    }

    public function test_partial_update_only_changes_provided_fields(): void
    {
        $user = User::factory()->create();
        $medication = Medication::factory()->create(['name' => 'Amoxicillin', 'strength' => '250', 'unit' => 'mg', 'form' => 'capsule']);

        $response = $this->actingAs($user)->putJson("/api/medications/{$medication->id}", [
            'strength' => '500',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('medications', [
            'id' => $medication->id,
            'name' => 'Amoxicillin',
            'strength' => '500',
            'unit' => 'mg',
            'form' => 'capsule',
        ]);
    }

    public function test_cannot_update_a_medication_that_does_not_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/medications/999', ['name' => 'X']);

        $response->assertNotFound();
    }

    public function test_guest_cannot_update_a_medication(): void
    {
        $medication = Medication::factory()->create();

        $response = $this->putJson("/api/medications/{$medication->id}", ['name' => 'X']);

        $response->assertUnauthorized();
    }
}
