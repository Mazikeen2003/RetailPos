<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupervisorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_authorize_cancellation(): void
    {
        $cashier = User::factory()->create();
        $supervisorRole = Role::create(['name' => 'Supervisor']);
        $supervisor = User::factory()->create([
            'email' => 'supervisor@test.com',
            'password' => 'password123',
            'role_id' => $supervisorRole->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($cashier);

        $this->postJson('/api/supervisor-authorizations', [
            'email' => $supervisor->email,
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Supervisor approval confirmed.');
    }

    public function test_cashier_cannot_authorize_cancellation(): void
    {
        $cashierRole = Role::create(['name' => 'Cashier']);
        $cashier = User::factory()->create(['role_id' => $cashierRole->id]);

        Sanctum::actingAs($cashier);

        $this->postJson('/api/supervisor-authorizations', [
            'email' => $cashier->email,
            'password' => 'password',
        ])->assertForbidden();
    }
}
