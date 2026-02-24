<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        $rows = MemorialNotice::query()
            ->published()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get(['city']);

        $cityPages = [];

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
