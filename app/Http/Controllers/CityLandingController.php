<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use App\Services\Geocoder;
use App\Support\NoticeSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityLandingController extends Controller
{
    public function city(Request $request, Geocoder $geocoder, string $city)
    {
        return $this->landing($request, $geocoder, $city, 'city');
    }

    public function crematorium(Request $request, Geocoder $geocoder, string $city)
    {
        return $this->landing($request, $geocoder, $city, 'crematorium');
    }

    private function landing(Request $request, Geocoder $geocoder, string $city, string $mode)
    {
        $cityName = $this->resolveCityName($city);
        $filters = NoticeSearch::fromRequest($request, $geocoder);

        $query = MemorialNotice::query()
            ->published()
            ->whereRaw('LOWER(city) = ?', [Str::of($cityName)->lower()->toString()]);

        return view('city.landing', [
            'city' => $city,
            'cityName' => $cityName,
            'latestNotices' => $filters->results($query),
            'filters' => $filters,
            'mode' => $mode,
        ]);
    }

    private function resolveCityName(string $city): string
    {
        $slug = Str::slug($city);

        $page = ContentPage::query()
            ->where('is_active', true)
            ->where('content_type', 'city')
            ->where('path', $slug)
            ->first(['title']);

        return $page?->title ?: Str::of($city)->replace('-', ' ')->title()->toString();
    }
}
