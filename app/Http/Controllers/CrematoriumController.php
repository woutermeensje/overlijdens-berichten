<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\RegionSubscription;
use App\Services\Geocoder;
use App\Support\CityResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Overzicht van crematoria (organisaties met categorie crematorium): heel Nederland op /crematorium,
 * per plaats op /crematorium/{stad}.
 */
class CrematoriumController extends Controller
{
    public function index(Request $request)
    {
        $province = in_array($request->query('provincie'), RegionSubscription::PROVINCES, true) ? (string) $request->query('provincie') : null;
        $place = Str::limit(trim((string) $request->query('plaats')), 120, '');

        // Een bekende plaats heeft een eigen pagina; daar hoort de zoekopdracht thuis.
        if ($place !== '' && ($cityName = CityResolver::name($place)) !== null) {
            return redirect()->route('city.crematorium', ['city' => Str::slug($cityName)]);
        }

        $crematoria = $this->crematoria()
            ->when($province, fn ($query) => $query->where('province', $province))
            ->when($place !== '', fn ($query) => $query->where('city', 'like', '%'.addcslashes($place, '%_\\').'%'))
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        return view('crematoria.index', [
            'cityName' => null,
            'crematoria' => $crematoria,
            'nearby' => collect(),
            'province' => $province,
            'filters' => ['plaats' => $place, 'provincie' => $province],
            'provinces' => RegionSubscription::PROVINCES,
        ]);
    }

    public function city(Geocoder $geocoder, string $city)
    {
        $cityName = CityResolver::name($city);
        abort_if($cityName === null, 404);

        $crematoria = $this->crematoria()
            ->whereRaw('LOWER(city) = ?', [Str::lower($cityName)])
            ->orderBy('name')
            ->get();

        $province = $crematoria->first()?->province ?? $geocoder->findPlace($cityName)['province'] ?? null;

        // Weinig of geen crematoria in de plaats zelf: toon ook die in de rest van de provincie.
        $nearby = $province === null ? collect() : $this->crematoria()
            ->where('province', $province)
            ->whereRaw('LOWER(city) != ?', [Str::lower($cityName)])
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        return view('crematoria.index', [
            'cityName' => $cityName,
            'crematoria' => $crematoria,
            'nearby' => $nearby,
            'province' => $province,
            'filters' => ['plaats' => $cityName, 'provincie' => null],
            'provinces' => RegionSubscription::PROVINCES,
        ]);
    }

    private function crematoria()
    {
        return Organization::query()->published()->where('category', 'crematorium');
    }
}
