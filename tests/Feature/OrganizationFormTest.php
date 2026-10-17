<?php

namespace Tests\Feature;

use App\Mail\AdminNewOrganizationMail;
use App\Models\Organization;
use App\Models\User;
use App\Support\FormTimer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationFormTest extends TestCase
{
    use RefreshDatabase;

    private const UTRECHT_ID = 'wpl-0123456789abcdef0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    protected function fakeGeocoder(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/lookup') && $request['id'] === self::UTRECHT_ID) {
                return Http::response(['response' => ['docs' => [[
                    'id' => self::UTRECHT_ID, 'type' => 'woonplaats', 'woonplaatsnaam' => 'Utrecht', 'provincienaam' => 'Utrecht',
                ]]]]);
            }

            return Http::response(['response' => ['docs' => []]]);
        });
    }

    private function submit(array $overrides = [])
    {
        $token = FormTimer::token();
        $this->travel(10)->seconds();

        return $this->post(route('organizations.store'), array_merge([
            'name' => 'Uitvaartzorg De Linde',
            'category' => 'uitvaartonderneming',
            'email' => 'info@delinde.nl',
            'website' => 'www.delinde.nl',
            'city' => 'Utrecht',
            'place_id' => self::UTRECHT_ID,
            'about' => 'Familiebedrijf dat al dertig jaar uitvaarten verzorgt in Utrecht en omgeving.',
            'form_token' => $token,
        ], $overrides));
    }

    public function test_form_page_is_shown_instead_of_old_content_page(): void
    {
        $this->get('/organisatie-toevoegen')
            ->assertOk()
            ->assertSee('<title>Organisatie toevoegen', false)
            ->assertSee('Uitvaartonderneming');
    }

    public function test_organization_is_saved_with_province_and_admin_is_mailed(): void
    {
        config(['app.admin_email' => null]);
        User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);

        $this->submit()->assertRedirect(route('organizations.create'))->assertSessionHas('organization_success');

        $organization = Organization::query()->sole();
        $this->assertSame('Utrecht', $organization->city);
        $this->assertSame('Utrecht', $organization->province);
        $this->assertSame('https://www.delinde.nl', $organization->website);
        $this->assertSame(Organization::STATUS_PUBLISHED, $organization->status);

        Mail::assertSent(AdminNewOrganizationMail::class, fn ($mail) => $mail->hasTo('admin@example.com')
            && str_contains($mail->render(), 'Uitvaartzorg De Linde'));
    }

    public function test_place_must_be_chosen_from_suggestions(): void
    {
        $this->submit(['city' => 'Nergenshuizen', 'place_id' => ''])->assertSessionHasErrors('city');

        $this->assertSame(0, Organization::count());
    }

    public function test_required_fields_and_category_are_validated(): void
    {
        $this->submit(['name' => '', 'category' => 'casino', 'email' => 'x', 'about' => 'kort'])
            ->assertSessionHasErrors(['name', 'category', 'email', 'about']);
    }

    public function test_honeypot_and_old_redirect(): void
    {
        $this->submit(['website_hp' => 'spam'])->assertSessionMissing('organization_success');
        $this->assertSame(0, Organization::count());

        $this->get('/overlijdensbericht-plaatsen')->assertRedirect('/bericht-plaatsen')->assertStatus(301);
    }

    public function test_overview_lists_published_organizations_with_filters(): void
    {
        $make = fn (string $name, string $category, string $city, string $province, string $status = Organization::STATUS_PUBLISHED) => Organization::create([
            'name' => $name, 'category' => $category, 'email' => 'info@example.com', 'city' => $city,
            'province' => $province, 'about' => 'Een organisatie uit de uitvaartbranche.', 'status' => $status,
        ]);
        $make('Uitvaartzorg De Linde', 'uitvaartonderneming', 'Utrecht', 'Utrecht');
        $make('Crematorium Daelwijck', 'crematorium', 'Utrecht', 'Utrecht');
        $make('Steenhouwerij Noord', 'steenhouwer', 'Groningen', 'Groningen');
        $make('Offline BV', 'uitvaartonderneming', 'Utrecht', 'Utrecht', 'offline');

        $this->get(route('organizations.index'))
            ->assertOk()
            ->assertSee('Uitvaartzorg De Linde')
            ->assertSee('Steenhouwerij Noord')
            ->assertDontSee('Offline BV');

        $this->get(route('organizations.index', ['type' => 'crematorium']))
            ->assertSee('Crematorium Daelwijck')
            ->assertDontSee('Uitvaartzorg De Linde')
            ->assertSee('<title>Crematorium |', false);

        $this->get(route('organizations.index', ['provincie' => 'Groningen']))
            ->assertSee('Steenhouwerij Noord')
            ->assertDontSee('Crematorium Daelwijck');

        $this->get(route('organizations.index', ['plaats' => 'utrecht', 'type' => 'casino']))
            ->assertSee('Crematorium Daelwijck')
            ->assertDontSee('Steenhouwerij Noord');
    }
}
