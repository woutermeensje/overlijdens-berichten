<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('cities.index'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('notice.place'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
        ];

        $notices = collect();
        if (Schema::hasTable('memorial_notices')) {
            $notices = MemorialNotice::query()
                ->published()
                ->whereNotNull('slug')
                ->get(['slug', 'published_at', 'updated_at']);
        }

        foreach ($notices as $notice) {
            $urls[] = [
                'loc' => route('notice.show', ['slug' => $notice->slug]),
                'lastmod' => optional($notice->updated_at ?? $notice->published_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        $blogPosts = collect();
        if (Schema::hasTable('content_pages')) {
            $blogPosts = ContentPage::query()
                ->where('is_active', true)
                ->where('content_type', 'blog')
                ->whereNotNull('slug')
                ->get(['slug', 'updated_at']);
        }

        foreach ($blogPosts as $post) {
            $urls[] = [
                'loc' => route('blog.show', ['slug' => $post->slug]),
                'lastmod' => optional($post->updated_at)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $contentPages = collect();
        if (Schema::hasTable('content_pages')) {
            $contentPages = ContentPage::query()
                ->where('is_active', true)
                ->where('content_type', '!=', 'blog')
                ->whereNotNull('path')
                ->where('path', '!=', '')
                ->get(['path', 'updated_at']);
        }

        foreach ($contentPages as $page) {
            $urls[] = [
                'loc' => route('pages.show', ['path' => $page->path]),
                'lastmod' => optional($page->updated_at)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $cities = collect();
        if (Schema::hasTable('memorial_notices')) {
            $cities = MemorialNotice::query()
                ->published()
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->pluck('city')
                ->map(fn (string $city) => Str::slug(trim($city)))
                ->filter()
                ->unique()
                ->values();
        }

        foreach ($cities as $citySlug) {
            $urls[] = [
                'loc' => route('city.show', ['city' => $citySlug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ];

            $urls[] = [
                'loc' => route('city.crematorium', ['city' => $citySlug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        return response()
            ->view('sitemap.index', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
