<?php

namespace App\Support;

use App\Models\MemorialNotice;
use App\Services\Geocoder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Zoek- en filterlogica voor de berichtenlijsten (naam, type en straal rond een locatie).
 */
class NoticeSearch
{
    public const RADII = [5, 10, 25, 50, 100];
    public const DEFAULT_RADIUS = 25;

    /**
     * @param  array{lat: float, lng: float, label: string}|null  $origin
     */
    private function __construct(
        public readonly string $q,
        public readonly string $type,
        public readonly string $location,
        public readonly int $radius,
        public readonly ?array $origin,
    ) {
    }

    public static function fromRequest(Request $request, Geocoder $geocoder): self
    {
        $type = (string) $request->query('type', '');
        if (! array_key_exists($type, MemorialNotice::typeLabels())) {
            $type = '';
        }

        $radius = (int) $request->query('straal', self::DEFAULT_RADIUS);
        if (! in_array($radius, self::RADII, true)) {
            $radius = self::DEFAULT_RADIUS;
        }

        $location = trim((string) $request->query('locatie', ''));

        return new self(
            q: trim((string) $request->query('q', '')),
            type: $type,
            location: $location,
            radius: $radius,
            origin: $location !== '' ? $geocoder->locate($location, Geocoder::TYPES_SEARCH) : null,
        );
    }

    public function isActive(): bool
    {
        return $this->q !== '' || $this->type !== '' || $this->location !== '';
    }

    public function locationNotFound(): bool
    {
        return $this->location !== '' && $this->origin === null;
    }

    /**
     * @return Collection<int, MemorialNotice>
     */
    public function results(Builder $query, int $limit = 30): Collection
    {
        $query
            ->when($this->q !== '', function (Builder $query): void {
                $like = '%'.$this->q.'%';

                $query->where(function (Builder $q) use ($like): void {
                    $q->where('title', 'like', $like)
                        ->orWhere('deceased_first_name', 'like', $like)
                        ->orWhere('deceased_last_name', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('province', 'like', $like);
                });
            })
            ->when($this->type !== '', fn (Builder $query) => $query->where('type', $this->type))
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->latest('id');

        if ($this->origin === null) {
            // Locatie onbekend bij PDOK: val terug op tekstueel zoeken in plaats/provincie.
            if ($this->location !== '') {
                $like = '%'.$this->location.'%';
                $query->where(fn (Builder $q) => $q->where('city', 'like', $like)->orWhere('province', 'like', $like));
            }

            return $query->take($limit)->get();
        }

        ['lat' => $lat, 'lng' => $lng] = $this->origin;
        $latDelta = $this->radius / 111.045;
        $lngDelta = $this->radius / (111.045 * cos(deg2rad($lat)));

        // Grove selectie op een bounding box in SQL, exacte afstand daarna in PHP.
        return $query
            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
            ->cursor()
            ->map(function (MemorialNotice $notice) use ($lat, $lng) {
                $notice->distance_km = Geocoder::distanceKm($lat, $lng, (float) $notice->latitude, (float) $notice->longitude);

                return $notice;
            })
            ->filter(fn (MemorialNotice $notice) => $notice->distance_km <= $this->radius)
            ->take($limit)
            ->collect();
    }
}
