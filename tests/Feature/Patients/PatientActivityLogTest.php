<?php

namespace Tests\Feature\Patients;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_patient_writes_an_activity_log(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/patients', [
            'name' => 'John Doe',
            'birthdate' => '1990-01-01',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'patient_created',
            'user_id' => $user->id,
            'subject_type' => 'patient',
            'subject_id' => $response->json('data.id'),
        ]);
    }

    public function test_updating_a_patient_writes_an_activity_log_with_changes(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->putJson("/api/patients/{$patient->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'patient_updated',
            'user_id' => $user->id,
            'subject_type' => 'patient',
            'subject_id' => $patient->id,
        ]);

        $log = ActivityLog::where('action', 'patient_updated')->first();
        $this->assertNotNull($log->changes);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertEquals('Old Name', $log->changes['name']['from']);
        $this->assertEquals('New Name', $log->changes['name']['to']);
    }

    public function test_updating_a_patient_with_no_real_changes_writes_no_changes_in_log(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Same Name']);

        $response = $this->actingAs($user)->putJson("/api/patients/{$patient->id}", [
            'name' => 'Same Name',
        ]);

        $response->assertOk();

        $log = ActivityLog::where('action', 'patient_updated')->first();
        $this->assertEmpty($log->changes);
    }

    public function test_deleting_a_patient_writes_an_activity_log(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = Patient::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/patients/{$patient->id}");

        $response->assertNoContent();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'patient_deleted',
            'user_id' => $admin->id,
            'subject_type' => 'patient',
            'subject_id' => $patient->id,
        ]);
    }
}
