<?php

namespace Tests\Feature;

use App\Models\MemorialNotice;
use App\Models\MemorialNoticeView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeViewsTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/605.1.15';

    private function notice(string $title, string $status = 'published'): MemorialNotice
    {
        return MemorialNotice::create([
            'user_id' => User::factory()->create()->id,
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'type' => MemorialNotice::TYPE_OVERLIJDENSBERICHT,
            'content' => 'Tekst',
            'status' => $status,
            'published_at' => now(),
            'latitude' => 52.0,
            'longitude' => 4.0,
        ]);
    }

    public function test_visiting_a_notice_counts_views_per_day(): void
    {
        $notice = $this->notice('Jan Jansen');

        $this->withHeader('User-Agent', self::BROWSER)->get(route('notice.show', $notice->slug))->assertOk();
        $this->withHeader('User-Agent', self::BROWSER)->get(route('notice.show', $notice->slug))->assertOk();

        $this->assertSame(1, MemorialNoticeView::count());
        $this->assertSame(2, MemorialNoticeView::first()->views);
    }

    public function test_bots_and_admins_are_not_counted(): void
    {
        $notice = $this->notice('Jan Jansen');

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')->get(route('notice.show', $notice->slug))->assertOk();
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->withHeader('User-Agent', self::BROWSER)
            ->get(route('notice.show', $notice->slug))
            ->assertOk();

        $this->assertSame(0, MemorialNoticeView::count());
    }

    public function test_dashboard_shows_views_of_the_last_60_days_per_notice(): void
    {
        $popular = $this->notice('Piet Pietersen');
        $quiet = $this->notice('Klaas Klaassen', 'draft');
        $old = $this->notice('Oud Bericht');

        $popular->views()->create(['date' => now()->toDateString(), 'views' => 10]);
        $popular->views()->create(['date' => now()->subDays(59)->toDateString(), 'views' => 5]);
        $popular->views()->create(['date' => now()->subDays(60)->toDateString(), 'views' => 100]);
        $quiet->views()->create(['date' => now()->subDays(3)->toDateString(), 'views' => 2]);
        $old->views()->create(['date' => now()->subDays(90)->toDateString(), 'views' => 50]);

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Piet Pietersen', 'Klaas Klaassen'])
            ->assertDontSee('Oud Bericht');

        $noticeViews = $response->viewData('noticeViews');
        $this->assertSame([15, 2], $noticeViews->pluck('recent_views')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(17, $response->viewData('totalViews'));
    }
}
