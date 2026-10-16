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
            'region' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'string', 'max:64'],
        ]);

        $email = $request->input('email');
        $region = null;
        $province = null;

        if (trim((string) $request->input('region')) !== '') {
            try {
                $place = $geocoder->placeById((string) $request->input('region_id'));
            } catch (Throwable $e) {
                Log::warning('Plaats controleren mislukt', ['region_id' => $request->input('region_id'), 'error' => $e->getMessage()]);

                return back()->withInput()->with('newsletter_error', 'We konden de plaats nu niet controleren. Probeer het later opnieuw.');
            }

            if ($place === null) {
                return back()->withInput()->with('newsletter_error', 'Kies een plaats uit de lijst met suggesties.');
            }

            $region = $place['name'];
            $province = $place['province'] ?? null;
        }

        $exists = RegionSubscription::where('email', $email)
            ->where('region', $region)
            ->exists();

        if ($exists) {
            return back()->with('newsletter_error', 'Dit e-mailadres is al aangemeld voor deze regio.');
        }

        $subscription = RegionSubscription::create([
            'email' => $email,
            'region' => $region,
            'province' => $province,
            'token' => Str::random(40),
        ]);

        Mail::to($email)->send(new SubscriptionConfirmationMail($subscription));

        $adminEmails = User::adminEmails();

        foreach ($adminEmails as $adminEmail) {
            Mail::to($adminEmail)->send(new AdminNewSubscriptionMail($subscription));
        }

        return back()->with('newsletter_success', 'U bent aangemeld! U ontvangt wekelijks de laatste overlijdensberichten' . ($region ? ' uit ' . $region : '') . '.');
    }

    public function destroy(string $token)
    {
        $subscription = RegionSubscription::where('token', $token)->firstOrFail();
        $subscription->delete();

        return redirect('/')->with('newsletter_success', 'U bent uitgeschreven van de wekelijkse updates.');
    }
}
