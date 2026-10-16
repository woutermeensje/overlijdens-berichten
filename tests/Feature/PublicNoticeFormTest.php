<?php

namespace Tests\Feature;

use App\Mail\AdminFeaturedNoticeMail;
use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicNoticeFormTest extends TestCase
{
    use RefreshDatabase;

    private const DELFT_ID = 'wpl-8a1b2c3d4e5f60718293a4b5c6d7e8f9';

    protected function fakeGeocoder(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/lookup') && $request['id'] === self::DELFT_ID) {
                return Http::response(['response' => ['docs' => [[
                    'id' => self::DELFT_ID,
                    'type' => 'woonplaats',
                    'woonplaatsnaam' => 'Delft',
                    'provincienaam' => 'Zuid-Holland',
                ]]]]);
            }

            return Http::response(['response' => ['docs' => []]]);
        });
    }

    public function test_form_shows_all_fields_on_one_page(): void
    {
        $this->get(route('notice.wizard'))
            ->assertOk()
            ->assertSee('name="deceased_name"', false)
            ->assertDontSee('name="deceased_last_name"', false)
            ->assertSee('name="type"', false)
            ->assertSee('name="content"', false)
            ->assertSee('Bericht publiceren')
            ->assertDontSee('Stap 1');
    }

    public function test_notice_is_published_in_a_single_submit(): void
    {
        Storage::fake('public');

        $this->post(route('notice.wizard.submit'), [
            'deceased_name' => 'Maria de Vries',
            'born_date' => '1940-03-01',
            'died_date' => '2026-10-10',
            'photo' => UploadedFile::fake()->image('maria.jpg'),
            'type' => MemorialNotice::TYPE_FAMILIEBERICHT,
            'city' => 'Delft',
            'place_id' => self::DELFT_ID,
            'content' => '<p>In liefdevolle herinnering.</p><script>alert(1)</script>',
            'placer_first_name' => 'Jan',
            'placer_last_name' => 'de Vries',
            'placer_email' => 'jan@example.com',
            'placer_phone' => '06-12345678',
            'condolence_url' => 'https://www.uitvaart.nl/condoleance/maria-de-vries',
        ])->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();

        $this->assertSame('Maria de Vries', $notice->title);
        $this->assertSame('https://www.uitvaart.nl/condoleance/maria-de-vries', $notice->condolence_url);
        $this->assertSame('maria-de-vries', $notice->slug);
        $this->assertSame(MemorialNotice::TYPE_FAMILIEBERICHT, $notice->type);
        $this->assertSame('published', $notice->status);
        $this->assertStringNotContainsString('<script>', $notice->content);
        $this->assertNotNull($notice->photo_url);
        $this->assertSame('jan@example.com', $notice->placer_email);
        $this->assertSame('06-12345678', $notice->placer_phone);
        $this->assertGuest();
    }

    public function test_placer_contact_details_are_required(): void
    {
        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), $this->validPayload(['placer_first_name' => '', 'placer_last_name' => '', 'placer_email' => 'geen-email']))
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors(['placer_first_name', 'placer_last_name', 'placer_email']);

        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_placer_can_create_an_account_and_edit_the_notice_afterwards(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'create_account' => '1',
            'password' => 'geheim-wachtwoord',
            'password_confirmation' => 'geheim-wachtwoord',
        ]))->assertRedirect(route('account.notices.index'));

        $user = User::query()->where('email', 'jan@example.com')->sole();
        $notice = MemorialNotice::query()->sole();

        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($notice->user->is($user));

        $this->get(route('account.notices.index'))->assertOk()->assertSee('Maria de Vries');
        $this->get(route('account.notices.edit', $notice))->assertOk();
        $this->get(route('account.dashboard'))->assertForbidden();
    }

    public function test_account_creation_requires_a_confirmed_password(): void
    {
        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), $this->validPayload([
                'create_account' => '1',
                'password' => 'geheim-wachtwoord',
                'password_confirmation' => 'iets-anders',
            ]))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_existing_email_cannot_create_a_second_account(): void
    {
        User::factory()->create(['email' => 'jan@example.com']);

        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), $this->validPayload([
                'create_account' => '1',
                'password' => 'geheim-wachtwoord',
                'password_confirmation' => 'geheim-wachtwoord',
            ]))
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors('placer_email');

        $this->assertGuest();
        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_logged_in_user_places_notice_on_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notice.wizard.submit'), $this->validPayload())
            ->assertRedirect(route('home'));

        $this->assertTrue(MemorialNotice::query()->sole()->user->is($user));
    }

    public function test_users_cannot_manage_notices_of_others(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload());
        $notice = MemorialNotice::query()->sole();

        $this->actingAs(User::factory()->create())
            ->get(route('account.notices.edit', $notice))
            ->assertForbidden();

        $this->delete(route('account.notices.destroy', $notice))->assertForbidden();
        $this->get(route('account.notices.index'))->assertOk()->assertDontSee('Maria de Vries');
    }

    public function test_missing_fields_are_reported_and_nothing_is_saved(): void
    {
        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), ['born_date' => '1940-03-01'])
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors(['deceased_name', 'died_date', 'type'])
            ->assertSessionDoesntHaveErrors(['born_date', 'city', 'province', 'content']);

        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_notice_can_be_placed_with_only_required_fields(): void
    {
        $this->post(route('notice.wizard.submit'), [
            'deceased_name' => 'Maria',
            'died_date' => '2026-10-10',
            'type' => MemorialNotice::TYPE_OVERLIJDENSBERICHT,
            'placer_first_name' => 'Jan',
            'placer_last_name' => 'de Vries',
            'placer_email' => 'jan@example.com',
        ])->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();

        $this->assertSame('Maria', $notice->title);
        $this->assertNull($notice->deceased_last_name);
        $this->assertNull($notice->born_date);
        $this->assertNull($notice->city);
    }

    public function test_in_memoriam_can_be_placed(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'type' => MemorialNotice::TYPE_IN_MEMORIAM,
        ]))->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();

        $this->assertSame(MemorialNotice::TYPE_IN_MEMORIAM, $notice->type);
        $this->assertFalse($notice->is_featured);
        $this->assertNull($notice->billing_details);
    }

    public function test_featured_notice_requires_billing_details(): void
    {
        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), $this->validPayload([
                'type' => MemorialNotice::FEATURED_OPTION,
            ]))
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors(['billing_name', 'billing_email', 'billing_address', 'billing_postcode', 'billing_city']);

        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_featured_notice_is_published_and_admins_are_mailed_to_invoice(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);

        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'type' => MemorialNotice::FEATURED_OPTION,
            'billing_name' => 'Jan de Vries',
            'billing_email' => 'jan@example.com',
            'billing_address' => 'Markt 1',
            'billing_postcode' => '2611 GP',
            'billing_city' => 'Delft',
        ]))->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();

        $this->assertSame(MemorialNotice::TYPE_OVERLIJDENSBERICHT, $notice->type);
        $this->assertSame('published', $notice->status);
        $this->assertTrue($notice->is_featured);
        $this->assertSame('jan@example.com', $notice->billing_details['email']);

        Mail::assertSent(AdminFeaturedNoticeMail::class, fn ($mail) => $mail->hasTo('admin@example.com'));
        Mail::assertSent(AdminFeaturedNoticeMail::class, fn ($mail) => str_contains($mail->render(), 'Contactgegevens plaatser'));
    }

    public function test_billing_fields_are_ignored_for_free_notices(): void
    {
        Mail::fake();

        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'billing_name' => 'Jan de Vries',
        ]))->assertRedirect(route('home'));

        $this->assertNull(MemorialNotice::query()->sole()->billing_details);
        Mail::assertNothingSent();
    }

    public function test_full_name_with_multiple_first_names_is_kept_intact(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'deceased_name' => '  Johanna Maria   Petronella van der Berg ',
        ]))->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();
        $this->assertSame('Johanna Maria Petronella van der Berg', $notice->title);
        $this->assertSame('johanna-maria-petronella-van-der-berg', $notice->slug);
        $this->get(route('notice.show', $notice->slug))->assertSee('Johanna Maria Petronella van der Berg');
    }

    public function test_city_and_province_come_from_the_chosen_place(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'city' => 'delft (getypt)',
            'province' => 'Verzonnen provincie',
        ]))->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();
        $this->assertSame('Delft', $notice->city);
        $this->assertSame('Zuid-Holland', $notice->province);
    }

    public function test_typed_place_without_choice_from_list_is_rejected(): void
    {
        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), $this->validPayload(['city' => 'Ergensdorp', 'place_id' => '']))
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors('city');

        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_typed_place_is_kept_when_pdok_is_unreachable(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('PDOK onbereikbaar'));

        $this->post(route('notice.wizard.submit'), $this->validPayload(['city' => 'Delft', 'place_id' => self::DELFT_ID]))
            ->assertSessionHasNoErrors();

        $notice = MemorialNotice::query()->sole();
        $this->assertSame('Delft', $notice->city);
        $this->assertNull($notice->province);
    }

    public function test_admins_see_placer_contact_details_but_owners_do_not(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'placer_phone' => '06-12345678',
            'create_account' => '1',
            'password' => 'geheim-wachtwoord',
            'password_confirmation' => 'geheim-wachtwoord',
        ]));
        $notice = MemorialNotice::query()->sole();

        $this->get(route('account.notices.edit', $notice))->assertOk()->assertDontSee('Geplaatst door');

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('account.notices.index'))->assertSee('jan@example.com');
        $this->get(route('account.notices.edit', $notice))
            ->assertSee('Geplaatst door')
            ->assertSee('06-12345678');
    }

    public function test_owner_can_take_notice_offline_and_back_online(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload([
            'create_account' => '1',
            'password' => 'geheim-wachtwoord',
            'password_confirmation' => 'geheim-wachtwoord',
        ]));
        $notice = MemorialNotice::query()->sole();

        $this->patch(route('account.notices.status', $notice))->assertSessionHas('status', 'Bericht is offline gehaald.');
        $this->assertSame('offline', $notice->fresh()->status);
        $this->get(route('notice.show', $notice->slug))->assertNotFound();
        $this->get(route('home'))->assertDontSee('Maria de Vries');

        $this->patch(route('account.notices.status', $notice));
        $this->assertSame('published', $notice->fresh()->status);
        $this->get(route('notice.show', $notice->slug))->assertOk();
    }

    public function test_users_cannot_take_notices_of_others_offline(): void
    {
        $this->post(route('notice.wizard.submit'), $this->validPayload());
        $notice = MemorialNotice::query()->sole();

        $this->actingAs(User::factory()->create())
            ->patch(route('account.notices.status', $notice))
            ->assertForbidden();

        $this->assertSame('published', $notice->fresh()->status);
    }

    public function test_header_shows_login_button_for_guests(): void
    {
        $this->get(route('home'))->assertSee(route('login'), false)->assertSee('Inloggen');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function test_options_page_lists_packages_and_preselects_featured(): void
    {
        $this->get(route('options'))
            ->assertOk()
            ->assertSee('Gratis')
            ->assertSee('€ 175,00')
            ->assertSee('Uitvaartondernemer')
            ->assertSee('In overleg');

        $this->get(route('notice.wizard', ['type' => MemorialNotice::FEATURED_OPTION]))
            ->assertOk()
            ->assertSee('value="'.MemorialNotice::FEATURED_OPTION.'"', false);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'deceased_name' => 'Maria de Vries',
            'born_date' => '1940-03-01',
            'died_date' => '2026-10-10',
            'type' => MemorialNotice::TYPE_OVERLIJDENSBERICHT,
            'city' => 'Delft',
            'place_id' => self::DELFT_ID,
            'content' => '<p>In liefdevolle herinnering.</p>',
            'placer_first_name' => 'Jan',
            'placer_last_name' => 'de Vries',
            'placer_email' => 'jan@example.com',
        ], $overrides);
    }
}
