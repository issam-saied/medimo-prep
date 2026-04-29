<?php

namespace Tests\Feature\Medications;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateMedicationTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [
        'name' => 'Paracetamol',
        'strength' => '500',
        'unit' => 'mg',
        'form' => 'tablet',
    ];

    public function test_authenticated_user_can_create_a_medication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/medications', $this->validPayload);

        $response->assertCreated();
        $this->assertDatabaseHas('medications', ['name' => 'Paracetamol', 'strength' => '500']);
    }

    public function test_guest_cannot_create_a_medication(): void
    {
        $response = $this->postJson('/api/medications', $this->validPayload);

        $response->assertUnauthorized();
    }

    public function test_name_is_required_to_create_a_medication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/medications', array_merge($this->validPayload, ['name' => '']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_strength_is_required_to_create_a_medication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/medications', array_merge($this->validPayload, ['strength' => '']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['strength']);
    }

    public function test_unit_is_required_to_create_a_medication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/medications', array_merge($this->validPayload, ['unit' => '']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['unit']);
    }

    public function test_form_is_required_to_create_a_medication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/medications', array_merge($this->validPayload, ['form' => '']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['form']);
    }
}
