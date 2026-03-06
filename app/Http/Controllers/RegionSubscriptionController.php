<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewSubscriptionMail;
use App\Mail\SubscriptionConfirmationMail;
use App\Models\RegionSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegionSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
        ]);

        $email = $request->input('email');
        $region = $request->input('region') ? trim($request->input('region')) : null;
        if ($region === '') {
            $region = null;
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
            'token' => Str::random(40),
        ]);

        Mail::to($email)->send(new SubscriptionConfirmationMail($subscription));

        Mail::to(config('app.admin_email'))->send(new AdminNewSubscriptionMail($subscription));

        return back()->with('newsletter_success', 'U bent aangemeld! U ontvangt wekelijks de laatste overlijdensberichten' . ($region ? ' uit ' . $region : '') . '.');
    }

    public function destroy(string $token)
    {
        $subscription = RegionSubscription::where('token', $token)->firstOrFail();
        $subscription->delete();

        return redirect('/')->with('newsletter_success', 'U bent uitgeschreven van de wekelijkse updates.');
    }
}
