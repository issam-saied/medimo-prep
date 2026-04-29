<?php

namespace Tests\Feature\Medications;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListMedicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_medications(): void
    {
        $user = User::factory()->create();
        Medication::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/medications');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_it_can_filter_medications_by_name(): void
    {
        $user = User::factory()->create();
        Medication::factory()->create(['name' => 'Paracetamol']);
        Medication::factory()->create(['name' => 'Ibuprofen']);

        $response = $this->actingAs($user)->getJson('/api/medications?name=Para');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Paracetamol');
    }

    public function test_it_can_filter_medications_by_form(): void
    {
        $user = User::factory()->create();
        Medication::factory()->create(['name' => 'Med A', 'form' => 'tablet']);
        Medication::factory()->create(['name' => 'Med B', 'form' => 'syrup']);

        $response = $this->actingAs($user)->getJson('/api/medications?form=syrup');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Med B');
    }

    public function test_guest_cannot_list_medications(): void
    {
        $response = $this->getJson('/api/medications');

        $response->assertUnauthorized();
    }
}
