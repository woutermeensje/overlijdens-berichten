<?php

namespace App\Http\Controllers;

use App\Models\RegionSubscription;
use Illuminate\Support\Facades\DB;

class SubscriptionOverviewController extends Controller
{
    public function index()
    {
        $map = json_decode((string) file_get_contents(resource_path('data/nl-provinces.json')), true);

        $perProvince = RegionSubscription::query()
            ->whereNotNull('province')
            ->select('province', DB::raw('count(*) as total'))
            ->groupBy('province')
            ->pluck('total', 'province')
            ->map(fn ($total) => (int) $total);

        // Alle twaalf provincies tonen, ook zonder aanmeldingen, op volgorde van aantal.
        $provinces = collect(array_keys($map['provinces']))
            ->mapWithKeys(fn (string $name) => [$name => $perProvince->get($name, 0)])
            ->sortDesc();

        return view('account.subscriptions.index', [
            'map' => $map,
            'provinces' => $provinces,
            'maxPerProvince' => max(1, $provinces->max()),
            'total' => RegionSubscription::count(),
            'uniqueEmails' => RegionSubscription::query()->distinct()->count('email'),
            'nationwide' => RegionSubscription::query()->whereNull('region')->whereNull('province')->count(),
            'provinceWide' => RegionSubscription::query()->whereNull('region')->whereNotNull('province')->count(),
            'withoutProvince' => RegionSubscription::query()->whereNotNull('region')->whereNull('province')->count(),
            'lastWeek' => RegionSubscription::query()->where('created_at', '>=', now()->subWeek())->count(),
            'subscriptions' => RegionSubscription::query()->latest()->latest('id')->paginate(50),
        ]);
    }
}
