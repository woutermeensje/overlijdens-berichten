<?php

namespace App\Console\Commands;

use App\Models\MemorialNotice;
use Illuminate\Console\Command;

class GeocodeNoticesCommand extends Command
{
    protected $signature = 'notices:geocode {--all : Ook berichten die al coördinaten hebben opnieuw geocoden}';

    protected $description = 'Vul de coördinaten van overlijdensberichten aan op basis van plaats en provincie';

    public function handle(): int
    {
        $query = MemorialNotice::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->when(! $this->option('all'), fn ($query) => $query->whereNull('latitude'));

        $found = 0;
        $missing = [];

        $this->withProgressBar($query->lazyById(), function (MemorialNotice $notice) use (&$found, &$missing): void {
            $notice->geocode();
            $notice->saveQuietly();

            if ($notice->latitude !== null) {
                $found++;
            } else {
                $missing[$notice->city] = true;
            }
        });

        $this->newLine(2);
        $this->info("{$found} berichten voorzien van coördinaten.");

        if ($missing !== []) {
            $this->warn('Niet gevonden plaatsen: '.implode(', ', array_keys($missing)));
        }

        return self::SUCCESS;
    }
}
