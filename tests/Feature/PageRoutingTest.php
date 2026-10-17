<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function page(string $path, string $title, string $type = 'page', ?string $meta = null): ContentPage
    {
        return ContentPage::create([
            'title' => $title, 'slug' => $path, 'path' => $path, 'content_type' => $type,
            'meta_description' => $meta, 'content_html' => "<p>Tekst van\n\t {$title}.</p>", 'is_active' => true,
        ]);
    }

    public function test_known_city_shows_city_landing(): void
    {
        $this->page('delft', 'Delft', 'city');

        $this->get('/delft')->assertOk()->assertSee('<title>Overlijdensberichten in Delft', false);
    }

    public function test_single_segment_content_page_is_not_shown_as_city(): void
    {
        $this->page('over-ons', 'Over ons');

        $this->get('/over-ons')
            ->assertOk()
            ->assertSee('<title>Over ons', false)
            ->assertDontSee('Overlijdensberichten in Over Ons')
            ->assertSee('<meta name="description" content="Tekst van Over ons.">', false);
    }

    public function test_unknown_paths_return_a_dutch_404(): void
    {
        $this->get('/bestaat-niet')->assertNotFound()->assertSee('Pagina niet gevonden');
        $this->get('/crematorium/bestaat-niet')->assertNotFound();
    }

    public function test_crematorium_pages_live_under_crematorium_and_old_urls_redirect(): void
    {
        $this->page('almelo', 'Almelo', 'city');

        $this->get('/crematorium/almelo')->assertOk()->assertSee('<title>Crematorium in Almelo', false);
        $this->get('/almelo/crematorium')->assertStatus(301)->assertRedirect('/crematorium/almelo');
    }

    public function test_crematorium_pages_list_crematoria_with_filter(): void
    {
        $this->page('almelo', 'Almelo', 'city');
        $make = fn (string $name, string $city, string $province, string $category = 'crematorium') => \App\Models\Organization::create([
            'name' => $name, 'category' => $category, 'email' => 'info@example.com', 'city' => $city, 'province' => $province,
            'about' => 'Een crematorium met een eigen aula.', 'status' => \App\Models\Organization::STATUS_PUBLISHED,
        ]);
        $make('Crematorium Almelo', 'Almelo', 'Overijssel');
        $make('Crematorium Enschede', 'Enschede', 'Overijssel');
        $make('Uitvaartzorg Almelo', 'Almelo', 'Overijssel', 'uitvaartonderneming');
        $make('Crematorium Groningen', 'Groningen', 'Groningen');

        $this->get('/crematorium/almelo')
            ->assertOk()
            ->assertSeeInOrder(['Crematoria in Almelo', 'Crematorium Almelo', 'Andere crematoria in Overijssel', 'Crematorium Enschede'])
            ->assertDontSee('Uitvaartzorg Almelo')
            ->assertDontSee('Crematorium Groningen');

        $this->get('/crematorium')->assertOk()->assertSee('<title>Crematoria in Nederland', false)->assertSee('Crematorium Groningen');
        $this->get('/crematorium?provincie=Groningen')->assertSee('Crematorium Groningen')->assertDontSee('Crematorium Enschede');
        $this->get('/crematorium?plaats=Almelo')->assertRedirect('/crematorium/almelo');
        $this->get('/crematorium/enschede')->assertOk()->assertSee('Crematorium Enschede');
    }
}
