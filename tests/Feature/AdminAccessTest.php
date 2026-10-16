<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => 'geheim123']);

        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'geheim123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_log_in(): void
    {
        $admin = User::factory()->create(['password' => 'geheim123', 'is_admin' => true]);

        $this->post(route('login.attempt'), ['email' => $admin->email, 'password' => 'geheim123'])
            ->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_account_area_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('account.notices.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('account.notices.index'))
            ->assertOk();
    }

    public function test_registration_is_removed(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('register'));
        $this->post('/account-aanmaken')->assertStatus(405);
    }
}
