<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewSubscriptionMail;
use App\Mail\SubscriptionConfirmationMail;
use App\Models\RegionSubscription;
use App\Models\User;
use App\Services\Geocoder;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class RegionSubscriptionController extends Controller
{
    /** Mensen hebben minimaal zoveel seconden nodig om het formulier in te vullen; bots niet. */
    private const MIN_FILL_SECONDS = 3;

    private const MAX_FORM_AGE_SECONDS = 6 * 60 * 60;

    private const MAX_AREAS = 10;

    /** Tijdstempel voor het formulier, zodat direct geposte of razendsnelle inzendingen herkend worden. */
    public static function formToken(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    public function store(Request $request, Geocoder $geocoder)
    {
        try {
            $age = now()->timestamp - (int) Crypt::decryptString((string) $request->input('form_token'));
        } catch (DecryptException) {
            return back();
        }

        if ($age < self::MIN_FILL_SECONDS) {
            return back();
        }

        if ($age > self::MAX_FORM_AGE_SECONDS) {
            return back()->withInput()->with('newsletter_error', 'Het formulier is verlopen. Probeer het opnieuw.');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'areas' => ['nullable', 'array', 'max:'.self::MAX_AREAS],
            'areas.*.type' => ['required', 'in:city,province'],
            'areas.*.name' => ['nullable', 'string', 'max:255'],
            'areas.*.id' => ['nullable', 'string', 'max:64'],
        ]);

        $email = $request->input('email');

        try {
            $areas = $this->resolveAreas($geocoder, (array) $request->input('areas', []));
        } catch (Throwable $e) {
            Log::warning('Plaats controleren mislukt', ['areas' => $request->input('areas'), 'error' => $e->getMessage()]);

            return back()->withInput()->with('newsletter_error', 'We konden de plaats nu niet controleren. Probeer het later opnieuw.');
        }

        if (is_string($areas)) {
            return back()->withInput()->with('newsletter_error', $areas);
        }

        $subscriptions = collect($areas)
            ->reject(fn (array $area): bool => $this->alreadySubscribed($email, $area))
            ->map(fn (array $area): RegionSubscription => RegionSubscription::create([
                'email' => $email,
                'region' => $area['region'],
                'province' => $area['province'],
                'token' => Str::random(40),
            ]))
            ->values();

        if ($subscriptions->isEmpty()) {
            return back()->with('newsletter_error', 'Dit e-mailadres is al aangemeld voor '.(count($areas) === 1 ? 'dit gebied' : 'deze gebieden').'.');
        }

        Mail::to($email)->send(new SubscriptionConfirmationMail($subscriptions));

        foreach (User::adminEmails() as $adminEmail) {
            Mail::to($adminEmail)->send(new AdminNewSubscriptionMail($subscriptions));
        }

        $labels = $subscriptions->map(fn (RegionSubscription $subscription): string => $subscription->areaLabel());

        return back()->with('newsletter_success', 'Je bent aangemeld! Je ontvangt wekelijks de laatste overlijdensberichten uit '.$labels->join(', ', ' en ').'.');
    }

    /**
     * Zet de gekozen steden en provincies om naar gebieden. Geen keuze = heel Nederland.
     *
     * @return list<array{region: string|null, province: string|null}>|string  een foutmelding als een keuze ongeldig is
     */
    private function resolveAreas(Geocoder $geocoder, array $input): array|string
    {
        $areas = [];

        foreach ($input as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            if (($row['type'] ?? null) === 'province') {
                if (! in_array($name, RegionSubscription::PROVINCES, true)) {
                    return 'Kies een provincie uit de lijst.';
                }
                $area = ['region' => null, 'province' => $name];
            } else {
                $place = $geocoder->placeById((string) ($row['id'] ?? ''));
                if ($place === null) {
                    return 'Kies een plaats uit de lijst met suggesties.';
                }
                $area = ['region' => $place['name'], 'province' => $place['province'] ?? null];
            }

            $areas[($area['region'] ?? '').'|'.($area['region'] === null ? $area['province'] : '')] = $area;
        }

        return $areas === [] ? [['region' => null, 'province' => null]] : array_values($areas);
    }

    /** Een stad is uniek per naam; een provincie of heel Nederland heeft geen stad. */
    private function alreadySubscribed(string $email, array $area): bool
    {
        return RegionSubscription::query()
            ->where('email', $email)
            ->where('region', $area['region'])
            ->when($area['region'] === null, fn ($query) => $query->where('province', $area['province']))
            ->exists();
    }

    public function destroy(string $token)
    {
        $subscription = RegionSubscription::where('token', $token)->firstOrFail();
        $subscription->delete();

        return redirect('/')->with('newsletter_success', 'Je bent uitgeschreven van de wekelijkse updates.');
    }
}
