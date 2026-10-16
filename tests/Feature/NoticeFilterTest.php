<?php

namespace Tests\Feature;

use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NoticeFilterTest extends TestCase
{
    use RefreshDatabase;

    private const PLACES = [
        'Delft' => 'POINT(4.36310563 51.99845672)',
        'Leiden' => 'POINT(4.49306 52.15833)',
        'Groningen' => 'POINT(6.56650 53.21917)',
    ];

    protected function fakeGeocoder(): void
    {
        Http::fake(function (Request $request) {
            foreach (self::PLACES as $place => $point) {
                if (str_starts_with((string) $request['q'], $place)) {
                    return Http::response(['response' => ['docs' => [[
                        'weergavenaam' => $place,
                        'centroide_ll' => $point,
                    ]]]]);
                }
            }

            return Http::response(['response' => ['docs' => []]]);
        });
    }

    public function test_notices_are_geocoded_when_saved(): void
    {
        $notice = $this->createNotice('Jan Delft', 'Delft');

        $this->assertEqualsWithDelta(51.998, $notice->latitude, 0.01);
        $this->assertEqualsWithDelta(4.363, $notice->longitude, 0.01);
    }

    public function test_notices_can_be_filtered_by_type(): void
    {
        $this->createNotice('Anna Overlijden', 'Delft', MemorialNotice::TYPE_OVERLIJDENSBERICHT);
        $this->createNotice('Bert Rouw', 'Delft', MemorialNotice::TYPE_ROUWADVERTENTIE);

        $this->get(route('home', ['type' => MemorialNotice::TYPE_ROUWADVERTENTIE]))
            ->assertOk()
            ->assertSee('Bert Rouw')
            ->assertDontSee('Anna Overlijden');
    }

    public function test_featured_notices_are_listed_first(): void
    {
        $this->createNotice('Oud Uitgelicht', 'Delft')->forceFill([
            'is_featured' => true,
            'published_at' => now()->subWeek(),
        ])->save();
        $this->createNotice('Nieuw Gewoon', 'Delft');

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Oud Uitgelicht', 'Nieuw Gewoon']);
    }

    public function test_notices_can_be_filtered_by_radius_around_a_place(): void
    {
        $this->createNotice('Dirk Delft', 'Delft');
        $this->createNotice('Lies Leiden', 'Leiden');
        $this->createNotice('Gerrit Groningen', 'Groningen');

        $this->get(route('home', ['locatie' => 'Delft', 'straal' => 25]))
            ->assertOk()
            ->assertSee('Dirk Delft')
            ->assertSee('Lies Leiden')
            ->assertDontSee('Gerrit Groningen');

        $this->get(route('home', ['locatie' => 'Delft', 'straal' => 10]))
            ->assertOk()
            ->assertSee('Dirk Delft')
            ->assertDontSee('Lies Leiden');
    }

    public function test_unknown_location_falls_back_to_place_name_search(): void
    {
        $this->createNotice('Olga Onbekend', 'Ergensdorp');
        $this->createNotice('Dirk Delft', 'Delft');

        $this->get(route('home', ['locatie' => 'Ergensdorp']))
            ->assertOk()
            ->assertSee('konden we niet vinden', false)
            ->assertSee('Olga Onbekend')
            ->assertDontSee('Dirk Delft');
    }

    private function createNotice(string $name, string $city, string $type = MemorialNotice::TYPE_OVERLIJDENSBERICHT): MemorialNotice
    {
        [$first, $last] = explode(' ', $name, 2);

        return MemorialNotice::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => $name,
            'slug' => str($name)->slug()->toString(),
            'type' => $type,
            'deceased_first_name' => $first,
            'deceased_last_name' => $last,
            'content' => '<p>In liefdevolle herinnering.</p>',
            'province' => 'Zuid-Holland',
            'city' => $city,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
