<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_logs_in_to_own_notices(): void
    {
        $user = User::factory()->create(['password' => 'geheim123']);

        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'geheim123'])
            ->assertRedirect(route('account.notices.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_log_in(): void
    {
        $admin = User::factory()->create(['password' => 'geheim123', 'is_admin' => true]);

        $this->post(route('login.attempt'), ['email' => $admin->email, 'password' => 'geheim123'])
            ->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_parts_of_account_area_are_admin_only(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('account.dashboard'))->assertForbidden();
        $this->get(route('account.notices.create'))->assertForbidden();
        $this->get(route('account.notices.index'))->assertOk();

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('account.dashboard'))->assertOk();
        $this->get(route('account.notices.create'))->assertOk();
    }

    public function test_registration_is_removed(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('register'));
        $this->post('/account-aanmaken')->assertStatus(405);
    }
}
