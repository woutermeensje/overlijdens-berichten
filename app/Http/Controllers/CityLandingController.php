<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityLandingController extends Controller
{
    public function city(Request $request, string $city)
    {
        $search = trim((string) $request->query('q', ''));

        $latestNotices = MemorialNotice::query()
            ->published()
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

        $cityName = Str::of($city)->replace('-', ' ')->title()->toString();

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

        $latestNotices = MemorialNotice::query()
            ->published()
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

        $cityName = Str::of($city)->replace('-', ' ')->title()->toString();

        return view('city.landing', [
            'city' => $city,
            'cityName' => $cityName,
            'latestNotices' => $latestNotices,
            'search' => $search,
            'mode' => 'crematorium',
        ]);
    }
}
