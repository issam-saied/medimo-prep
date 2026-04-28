<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_writes_an_activity_log(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_login',
            'user_id' => $user->id,
            'subject_type' => 'user',
            'subject_id' => $user->id,
        ]);
    }

    public function test_failed_login_does_not_write_an_activity_log(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('activity_logs', [
            'action' => 'user_login',
            'user_id' => $user->id,
        ]);
    }

    public function test_logout_writes_an_activity_log(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/logout');

        $response->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_logout',
            'user_id' => $user->id,
            'subject_type' => 'user',
            'subject_id' => $user->id,
        ]);
    }
}
