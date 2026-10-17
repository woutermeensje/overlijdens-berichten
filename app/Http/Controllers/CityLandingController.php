<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use App\Services\Geocoder;
use App\Support\CityResolver;
use App\Support\NoticeSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityLandingController extends Controller
{
    public function city(Request $request, Geocoder $geocoder, string $city)
    {
        $cityName = CityResolver::name($city);

        // Geen bekende stad: dan is het een gewone contentpagina (zoals /over-ons), of een 404.
        if ($cityName === null) {
            return app(PageController::class)->show($city);
        }

        $filters = NoticeSearch::fromRequest($request, $geocoder);

        $query = MemorialNotice::query()
            ->published()
            ->whereRaw('LOWER(city) = ?', [Str::of($cityName)->lower()->toString()]);

        return view('city.landing', [
            'city' => $city,
            'cityName' => $cityName,
            'latestNotices' => $filters->results($query),
            'filters' => $filters,
            'mode' => 'city',
        ]);
    }
}
