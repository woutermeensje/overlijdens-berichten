<?php

namespace Tests\Feature;

use App\Http\Controllers\RegionSubscriptionController;
use App\Mail\AdminNewSubscriptionMail;
use App\Mail\SubscriptionConfirmationMail;
use App\Models\RegionSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegionSubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private const AMSTERDAM_ID = 'wpl-62fe60a4eea50b7c33ba3d9c39a21e6a';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    protected function fakeGeocoder(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/lookup') && $request['id'] === self::AMSTERDAM_ID) {
                return Http::response(['response' => ['docs' => [[
                    'id' => self::AMSTERDAM_ID,
                    'type' => 'woonplaats',
                    'woonplaatsnaam' => 'Amsterdam',
                    'provincienaam' => 'Noord-Holland',
                ]]]]);
            }

            return Http::response(['response' => ['docs' => []]]);
        });
    }

    /** Een formulier dat even geleden geopend is, zoals bij een echte bezoeker. */
    private function subscribe(array $data)
    {
        $token = RegionSubscriptionController::formToken();
        $this->travel(10)->seconds();

        return $this->post(route('newsletter.subscribe'), $data + ['form_token' => $token]);
    }

    public function test_admin_users_receive_a_notification_for_new_subscriptions(): void
    {
        config(['app.admin_email' => null]);

        User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $response = $this->subscribe([
            'email' => 'subscriber@example.com',
            'areas' => [['type' => 'city', 'name' => 'Amsterdam', 'id' => self::AMSTERDAM_ID]],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_success');
        $this->assertDatabaseHas('region_subscriptions', ['email' => 'subscriber@example.com', 'region' => 'Amsterdam', 'province' => 'Noord-Holland']);

        Mail::assertSent(SubscriptionConfirmationMail::class, fn (SubscriptionConfirmationMail $mail): bool => $mail->hasTo('subscriber@example.com'));
        Mail::assertSent(AdminNewSubscriptionMail::class, fn (AdminNewSubscriptionMail $mail): bool => $mail->hasTo('admin@example.com'));
    }

    public function test_subscribing_without_region_is_allowed(): void
    {
        $this->subscribe(['email' => 'subscriber@example.com'])
            ->assertSessionHas('newsletter_success');

        $this->assertDatabaseHas('region_subscriptions', ['email' => 'subscriber@example.com', 'region' => null]);
    }

    public function test_region_must_be_chosen_from_the_suggestions(): void
    {
        $this->subscribe(['email' => 'subscriber@example.com', 'areas' => [['type' => 'city', 'name' => 'egdurtwnpn']]])
            ->assertSessionHas('newsletter_error');

        $this->subscribe(['email' => 'subscriber@example.com', 'areas' => [['type' => 'city', 'name' => 'egdurtwnpn', 'id' => 'wpl-00000000000000000000000000000000']]])
            ->assertSessionHas('newsletter_error');

        $this->assertSame(0, RegionSubscription::count());
        Mail::assertNothingSent();
    }

    public function test_submissions_without_form_token_are_ignored(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'bot@example.com'])
            ->assertSessionMissing('newsletter_success');

        $this->assertSame(0, RegionSubscription::count());
    }

    public function test_submissions_that_are_too_fast_are_ignored(): void
    {
        $this->post(route('newsletter.subscribe'), [
            'email' => 'bot@example.com',
            'form_token' => RegionSubscriptionController::formToken(),
        ])->assertSessionMissing('newsletter_success');

        $this->assertSame(0, RegionSubscription::count());
    }

    public function test_multiple_cities_and_provinces_in_one_go(): void
    {
        $this->subscribe([
            'email' => 'subscriber@example.com',
            'areas' => [
                ['type' => 'city', 'name' => 'Amsterdam', 'id' => self::AMSTERDAM_ID],
                ['type' => 'province', 'name' => 'Utrecht'],
                ['type' => 'province', 'name' => 'Utrecht'],
                ['type' => 'city', 'name' => ''],
            ],
        ])->assertSessionHas('newsletter_success', 'U bent aangemeld! U ontvangt wekelijks de laatste overlijdensberichten uit Amsterdam en provincie Utrecht.');

        $this->assertDatabaseHas('region_subscriptions', ['email' => 'subscriber@example.com', 'region' => 'Amsterdam', 'province' => 'Noord-Holland']);
        $this->assertDatabaseHas('region_subscriptions', ['email' => 'subscriber@example.com', 'region' => null, 'province' => 'Utrecht']);
        $this->assertSame(2, RegionSubscription::count());

        // Eén bevestigingsmail met een afmeldlink per gebied.
        Mail::assertSent(SubscriptionConfirmationMail::class, 1);
        Mail::assertSent(SubscriptionConfirmationMail::class, fn (SubscriptionConfirmationMail $mail): bool => $mail->subscriptions->count() === 2
            && str_contains($mail->render(), 'Afmelden voor provincie Utrecht'));
    }

    public function test_province_must_be_one_of_the_twelve(): void
    {
        $this->subscribe(['email' => 'subscriber@example.com', 'areas' => [['type' => 'province', 'name' => 'Vlaanderen']]])
            ->assertSessionHas('newsletter_error', 'Kies een provincie uit de lijst.');

        $this->assertSame(0, RegionSubscription::count());
    }

    public function test_existing_areas_are_skipped(): void
    {
        $this->subscribe(['email' => 'subscriber@example.com', 'areas' => [['type' => 'province', 'name' => 'Utrecht']]]);

        $this->subscribe(['email' => 'subscriber@example.com', 'areas' => [['type' => 'province', 'name' => 'Utrecht']]])
            ->assertSessionHas('newsletter_error');

        $this->subscribe(['email' => 'subscriber@example.com', 'areas' => [
            ['type' => 'province', 'name' => 'Utrecht'],
            ['type' => 'province', 'name' => 'Zeeland'],
        ]])->assertSessionHas('newsletter_success');

        $this->assertSame(2, RegionSubscription::count());
    }
}
