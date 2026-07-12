<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\MemorialNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityLandingController extends Controller
{
    public function city(Request $request, string $city)
    {
        $search = trim((string) $request->query('q', ''));
        $cityName = $this->resolveCityName($city);
        $normalizedCity = Str::of($cityName)->lower()->toString();

        $latestNotices = MemorialNotice::query()
            ->published()
            ->whereRaw('LOWER(city) = ?', [$normalizedCity])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $like = '%'.$search.'%';

                    $q->where('title', 'like', $like)
                        ->orWhere('deceased_first_name', 'like', $like)
                        ->orWhere('deceased_last_name', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('province', 'like', $like);
                });
            })
            ->latest('published_at')
            ->latest('id')
            ->take(30)
            ->get();

        return view('city.landing', [
            'city' => $city,
            'cityName' => $cityName,
            'latestNotices' => $latestNotices,
            'search' => $search,
            'mode' => 'city',
        ]);
    }

    public function crematorium(Request $request, string $city)
    {
        $search = trim((string) $request->query('q', ''));
        $cityName = $this->resolveCityName($city);
        $normalizedCity = Str::of($cityName)->lower()->toString();

        $latestNotices = MemorialNotice::query()
            ->published()
            ->whereRaw('LOWER(city) = ?', [$normalizedCity])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $like = '%'.$search.'%';

                    $q->where('title', 'like', $like)
                        ->orWhere('deceased_first_name', 'like', $like)
                        ->orWhere('deceased_last_name', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('province', 'like', $like);
                });
            })
            ->latest('published_at')
            ->latest('id')
            ->take(30)
            ->get();

        return view('city.landing', [
            'city' => $city,
            'cityName' => $cityName,
            'latestNotices' => $latestNotices,
            'search' => $search,
            'mode' => 'crematorium',
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
