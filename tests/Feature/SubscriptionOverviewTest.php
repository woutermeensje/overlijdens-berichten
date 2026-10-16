<?php

namespace Tests\Feature;

use App\Models\RegionSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function subscription(string $email, ?string $region, ?string $province): RegionSubscription
    {
        return RegionSubscription::create(['email' => $email, 'region' => $region, 'province' => $province, 'token' => Str::random(40)]);
    }

    public function test_only_admins_can_see_the_overview(): void
    {
        $this->get(route('account.subscriptions'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('account.subscriptions'))
            ->assertForbidden();
    }

    public function test_overview_lists_subscriptions_and_counts_per_province(): void
    {
        $this->subscription('a@example.com', 'Delft', 'Zuid-Holland');
        $this->subscription('b@example.com', 'Leiden', 'Zuid-Holland');
        $this->subscription('c@example.com', 'Leeuwarden', 'Fryslân');
        $this->subscription('d@example.com', null, null);

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('account.subscriptions'))
            ->assertOk()
            ->assertSee('a@example.com')
            ->assertSee('Heel Nederland');

        $provinces = $response->viewData('provinces');
        $this->assertCount(12, $provinces);
        $this->assertSame(2, $provinces['Zuid-Holland']);
        $this->assertSame(1, $provinces['Fryslân']);
        $this->assertSame(0, $provinces['Limburg']);
        $this->assertSame('Zuid-Holland', $provinces->keys()->first());
    }
}
