<?php

namespace Tests\Feature;

use App\Mail\AdminFeaturedNoticeMail;
use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicNoticeFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_shows_all_fields_on_one_page(): void
    {
        $this->get(route('notice.wizard'))
            ->assertOk()
            ->assertSee('name="deceased_first_name"', false)
            ->assertSee('name="type"', false)
            ->assertSee('name="content"', false)
            ->assertSee('Bericht publiceren')
            ->assertDontSee('Stap 1');
    }

    public function test_notice_is_published_in_a_single_submit(): void
    {
        Storage::fake('public');

        $this->post(route('notice.wizard.submit'), [
            'deceased_first_name' => 'Maria',
            'deceased_last_name' => 'de Vries',
            'born_date' => '1940-03-01',
            'died_date' => '2026-10-10',
            'photo' => UploadedFile::fake()->image('maria.jpg'),
            'type' => MemorialNotice::TYPE_FAMILIEBERICHT,
            'city' => 'Delft',
            'province' => 'Zuid-Holland',
            'content' => '<p>In liefdevolle herinnering.</p><script>alert(1)</script>',
        ])->assertRedirect(route('home'));

        $notice = MemorialNotice::query()->sole();

        $this->assertSame('Maria de Vries', $notice->title);
        $this->assertSame('maria-de-vries', $notice->slug);
        $this->assertSame(MemorialNotice::TYPE_FAMILIEBERICHT, $notice->type);
        $this->assertSame('published', $notice->status);
        $this->assertStringNotContainsString('<script>', $notice->content);
        $this->assertNotNull($notice->photo_url);
    }

    public function test_missing_fields_are_reported_and_nothing_is_saved(): void
    {
        $this->from(route('notice.wizard'))
            ->post(route('notice.wizard.submit'), ['deceased_last_name' => 'de Vries'])
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors(['deceased_first_name', 'died_date', 'type'])
            ->assertSessionDoesntHaveErrors(['deceased_last_name', 'born_date', 'city', 'province', 'content']);

        $this->assertDatabaseCount('memorial_notices', 0);
    }

    public function test_notice_can_be_placed_with_only_required_fields(): void
    {
        $this->post(route('notice.wizard.submit'), [
            'deceased_first_name' => 'Maria',
            'died_date' => '2026-10-10',
            'type' => MemorialNotice::TYPE_OVERLIJDENSBERICHT,
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'deceased_first_name' => 'Maria',
            'deceased_last_name' => 'de Vries',
            'born_date' => '1940-03-01',
            'died_date' => '2026-10-10',
            'type' => MemorialNotice::TYPE_OVERLIJDENSBERICHT,
            'city' => 'Delft',
            'province' => 'Zuid-Holland',
            'content' => '<p>In liefdevolle herinnering.</p>',
        ], $overrides);
    }
}
