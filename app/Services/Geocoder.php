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
    private const LOOKUP_ENDPOINT = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/lookup';

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

    /**
     * Zoekt de PDOK-woonplaats bij een plaatsnaam, zodat een formulier die vooraf kan selecteren.
     *
     * @return array{id: string, name: string}|null
     */
    public function findPlace(string $name): ?array
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $result = Cache::remember('geocoder:place:'.md5(Str::lower($name)), now()->addDays(30), function () use ($name) {
            try {
                $response = Http::timeout(5)->get(self::ENDPOINT, [
                    'q' => $name,
                    'fq' => 'type:woonplaats',
                    'rows' => 1,
                    'fl' => 'id,woonplaatsnaam',
                ]);
            } catch (Throwable $e) {
                Log::warning('Geocoding mislukt', ['query' => $name, 'error' => $e->getMessage()]);

                return ['match' => null];
            }

            $doc = $response->successful() ? $response->json('response.docs.0') : null;

            return ['match' => is_array($doc) && isset($doc['id'], $doc['woonplaatsnaam'])
                ? ['id' => (string) $doc['id'], 'name' => (string) $doc['woonplaatsnaam']]
                : null];
        });

        return $result['match'];
    }

    /**
     * Haalt een woonplaats op via het PDOK-id dat de gebruiker in de suggestielijst koos.
     *
     * @return array{id: string, name: string, province: string|null}|null  null als het id geen bestaande woonplaats is
     *
     * @throws \Illuminate\Http\Client\ConnectionException|\Illuminate\Http\Client\RequestException als PDOK niet bereikbaar is
     */
    public function placeById(string $id): ?array
    {
        if (! preg_match('/^wpl-[a-f0-9]{32}$/', $id)) {
            return null;
        }

        $result = Cache::remember('geocoder:place-id:v2:'.$id, now()->addDays(30), function () use ($id) {
            $doc = Http::timeout(5)
                ->get(self::LOOKUP_ENDPOINT, ['id' => $id, 'fl' => 'id,type,woonplaatsnaam,provincienaam'])
                ->throw()
                ->json('response.docs.0');

            return ['match' => is_array($doc) && ($doc['type'] ?? null) === 'woonplaats' && isset($doc['woonplaatsnaam'])
                ? ['id' => $id, 'name' => (string) $doc['woonplaatsnaam'], 'province' => $doc['provincienaam'] ?? null]
                : null];
        });

        return $result['match'];
    }

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
