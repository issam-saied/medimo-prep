<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'job_title' => 'Nurse',
            'organization' => 'Old Hospital',
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'job_title' => 'Doctor',
            'organization' => 'New Hospital',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'job_title' => 'Doctor',
            'organization' => 'New Hospital',
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
        ]);

        $response->assertOk();
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'wrong_password',
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'new_password_123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_use_an_email_that_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => $user->name,
            'email' => 'taken@example.com',
            'job_title' => $user->job_title,
            'organization' => $user->organization,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertUnauthorized();
    }

    public function test_name_is_required_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'job_title' => 'Nurse',
            'organization' => 'Old Hospital',
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => '',
            'email' => 'new@example.com',
            'job_title' => 'Doctor',
            'organization' => 'New Hospital',
        ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors(['name']);
    }
}
