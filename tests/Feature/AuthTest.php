<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.id', $user->id);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_authenticated_user_can_fetch_me(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertOk();
        $response->assertJsonPath('user.id', $user->id);
    }

    public function test_guest_cannot_fetch_me(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertUnauthorized();
    }
}
