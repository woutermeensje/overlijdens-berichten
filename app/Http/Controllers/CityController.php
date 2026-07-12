<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        $cityPages = ContentPage::query()
            ->where('is_active', true)
            ->where('content_type', 'city')
            ->whereNotNull('path')
            ->where('path', '!=', '')
            ->where('path', 'not like', '%/%')
            ->get(['title', 'path'])
            ->mapWithKeys(function (ContentPage $page): array {
                $citySlug = Str::slug($page->path);

                if ($citySlug === '') {
                    return [];
                }

                return [
                    $citySlug => [
                        'name' => $page->title ?: Str::title(str_replace('-', ' ', $citySlug)),
                        'slug' => $citySlug,
                        'count' => 0,
                    ],
                ];
            })
            ->all();

        $rows = MemorialNotice::query()
            ->published()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get(['city']);

        foreach ($rows as $row) {
            $cityName = trim((string) $row->city);
            $citySlug = Str::slug($cityName);
            if ($citySlug === '') {
                continue;
            }

            if (!isset($cityPages[$citySlug])) {
                $cityPages[$citySlug] = [
                    'name' => Str::title($cityName),
                    'slug' => $citySlug,
                    'count' => 0,
                ];
            }

            $cityPages[$citySlug]['count']++;
        }

        uasort($cityPages, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return view('cities.index', [
            'cities' => array_values($cityPages),
        ]);
    }
}
