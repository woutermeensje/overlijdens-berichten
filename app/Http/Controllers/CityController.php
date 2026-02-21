<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        $rows = ContentPage::query()
            ->where('is_active', true)
            ->where('content_type', 'page')
            ->where('path', 'like', '%/%')
            ->get(['path']);

        $cityPages = [];

        foreach ($rows as $row) {
            $segments = array_values(array_filter(explode('/', (string) $row->path)));
            if (count($segments) < 2) {
                continue;
            }

            $citySlug = $segments[0];
            $cityName = Str::title(str_replace('-', ' ', $citySlug));

            if (!isset($cityPages[$citySlug])) {
                $cityPages[$citySlug] = [
                    'name' => $cityName,
                    'slug' => $citySlug,
                    'count' => 0,
                    'example_path' => $row->path,
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
