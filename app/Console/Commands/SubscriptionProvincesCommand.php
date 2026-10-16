<?php

namespace App\Console\Commands;

use App\Models\RegionSubscription;
use App\Services\Geocoder;
use Illuminate\Console\Command;

class SubscriptionProvincesCommand extends Command
{
    protected $signature = 'subscriptions:provinces';

    protected $description = 'Vul de provincie aan bij alert-aanmeldingen van vóór de plaatskeuze uit PDOK';

    public function handle(Geocoder $geocoder): int
    {
        $query = RegionSubscription::query()
            ->whereNull('province')
            ->whereNotNull('region')
            ->where('region', '!=', '');

        $found = 0;
        $missing = [];

        $this->withProgressBar($query->lazyById(), function (RegionSubscription $subscription) use ($geocoder, &$found, &$missing): void {
            $province = $geocoder->findPlace($subscription->region)['province'] ?? null;

            if ($province === null) {
                $missing[$subscription->region] = true;

                return;
            }

            $subscription->forceFill(['province' => $province])->saveQuietly();
            $found++;
        });

        $this->newLine(2);
        $this->info("{$found} aanmeldingen voorzien van een provincie.");

        if ($missing !== []) {
            $this->warn('Niet gevonden plaatsen: '.implode(', ', array_keys($missing)));
        }

        return self::SUCCESS;
    }
}
