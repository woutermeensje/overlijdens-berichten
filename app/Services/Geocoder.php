<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Zet Nederlandse plaatsnamen en postcodes om naar coördinaten via de PDOK Locatieserver.
 */
class Geocoder
{
    private const ENDPOINT = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free';

    public const TYPES_PLACE = ['woonplaats'];
    public const TYPES_SEARCH = ['woonplaats', 'postcode', 'gemeente'];

    /**
     * @param  list<string>  $types
     * @return array{lat: float, lng: float, label: string}|null
     */
    public function locate(string $query, array $types = self::TYPES_PLACE): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $cacheKey = 'geocoder:'.md5(Str::lower($query).'|'.implode(',', $types));

        $result = Cache::remember($cacheKey, now()->addDays(30), function () use ($query, $types) {
            return ['match' => $this->lookup($query, $types)];
        });

        return $result['match'];
    }

    private function lookup(string $query, array $types): ?array
    {
        $params = [
            'q' => $query,
            'fq' => 'type:('.implode(' OR ', $types).')',
            'rows' => 1,
            'fl' => 'weergavenaam,woonplaatsnaam,centroide_ll',
            'bq' => 'type:woonplaats^2',
        ];

        // Postcodes (4 cijfers, optioneel met letters) moeten op het postcodeveld gefilterd worden;
        // vrij zoeken op "2611" levert anders willekeurige plaatsen op.
        $postcode = null;
        if (in_array('postcode', $types, true) && preg_match('/^(\d{4})\s*([a-z]{0,2})$/i', $query, $pc)) {
            $postcode = $pc[1].Str::upper($pc[2]);
            $params['q'] = '*:*';
            $params['fq'] = ['type:postcode', 'postcode:'.$postcode.'*'];
        }

        try {
            // PDOK verwacht herhaalde fq-parameters, niet fq[]=...
            $url = self::ENDPOINT.'?'.preg_replace('/%5B\d+%5D=/', '=', http_build_query($params));
            $response = Http::timeout(5)->get($url);
        } catch (Throwable $e) {
            Log::warning('Geocoding mislukt', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }

        $doc = $response->successful() ? $response->json('response.docs.0') : null;

        if (! is_array($doc) || ! preg_match('/POINT\(([-\d.]+) ([-\d.]+)\)/', (string) ($doc['centroide_ll'] ?? ''), $m)) {
            return null;
        }

        $label = $postcode !== null
            ? trim($postcode.' '.($doc['woonplaatsnaam'] ?? ''))
            : (string) ($doc['woonplaatsnaam'] ?? $doc['weergavenaam'] ?? $query);

        return [
            'lat' => (float) $m[2],
            'lng' => (float) $m[1],
            'label' => $label,
        ];
    }

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
