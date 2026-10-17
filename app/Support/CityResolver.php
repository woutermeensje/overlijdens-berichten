<?php

namespace App\Support;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use App\Models\Organization;
use Illuminate\Support\Str;

/**
 * Zet een stads-slug uit de URL om naar een plaatsnaam, maar alleen voor plaatsen die de site kent.
 */
class CityResolver
{
    /**
     * Bekend = er is een stadspagina, een gepubliceerd bericht of een gepubliceerde organisatie in die plaats.
     */
    public static function name(string $city): ?string
    {
        $slug = Str::slug($city);

        $page = ContentPage::query()
            ->where('is_active', true)
            ->where('content_type', 'city')
            ->where('path', $slug)
            ->first(['title']);

        if ($page !== null) {
            return $page->title ?: Str::of($slug)->replace('-', ' ')->title()->toString();
        }

        $matches = fn (string $name): bool => Str::slug($name) === $slug;

        return MemorialNotice::query()->published()->whereNotNull('city')->distinct()->pluck('city')->first($matches)
            ?? Organization::query()->published()->distinct()->pluck('city')->first($matches);
    }
}
