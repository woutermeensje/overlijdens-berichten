<?php

namespace App\Providers;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.public', function ($view): void {
            if (!Schema::hasTable('memorial_notices') && !Schema::hasTable('content_pages')) {
                $view->with('footerCities', []);

                return;
            }

            $cityMap = [];

            if (Schema::hasTable('content_pages')) {
                $paths = ContentPage::query()
                    ->where('is_active', true)
                    ->where('path', 'like', '%/%')
                    ->pluck('path');

                foreach ($paths as $path) {
                    $segments = array_values(array_filter(explode('/', (string) $path)));
                    if ($segments === []) {
                        continue;
                    }

                    $slug = trim((string) $segments[0]);
                    if ($slug === '') {
                        continue;
                    }

                    $citySlug = Str::slug($slug);
                    if ($citySlug === '') {
                        continue;
                    }

                    $cityMap[$citySlug] = [
                        'name' => Str::title(str_replace('-', ' ', $slug)),
                        'slug' => $citySlug,
                    ];
                }
            }

            if (Schema::hasTable('memorial_notices')) {
                $noticeCities = MemorialNotice::query()
                    ->published()
                    ->whereNotNull('city')
                    ->where('city', '!=', '')
                    ->pluck('city');

                foreach ($noticeCities as $cityName) {
                    $cityName = trim((string) $cityName);
                    if ($cityName === '') {
                        continue;
                    }

                    $citySlug = Str::slug($cityName);
                    if ($citySlug === '') {
                        continue;
                    }

                    $cityMap[$citySlug] = [
                        'name' => Str::title($cityName),
                        'slug' => $citySlug,
                    ];
                }
            }

            $cities = collect($cityMap)
                ->sortBy('name')
                ->values()
                ->all();

            $view->with('footerCities', $cities);
        });
    }
}
