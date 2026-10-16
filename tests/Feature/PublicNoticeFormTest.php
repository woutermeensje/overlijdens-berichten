<?php

namespace Tests\Feature;

use App\Models\MemorialNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->post(route('notice.wizard.submit'), ['deceased_first_name' => 'Maria'])
            ->assertRedirect(route('notice.wizard'))
            ->assertSessionHasErrors(['deceased_last_name', 'type', 'city', 'content']);

        $this->assertDatabaseCount('memorial_notices', 0);
    }
}
