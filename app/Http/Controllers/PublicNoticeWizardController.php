<?php

namespace App\Http\Controllers;

use App\Mail\AdminFeaturedNoticeMail;
use App\Mail\AdminNewNoticeMail;
use App\Models\MemorialNotice;
use App\Models\User;
use App\Services\Geocoder;
use App\Support\NoticeContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class PublicNoticeWizardController extends Controller
{
    public function show()
    {
        return view('notice-wizard.form');
    }

    public function submit(Request $request, Geocoder $geocoder): RedirectResponse
    {
        $data = $request->validate([
            'deceased_name' => ['required', 'string', 'max:120'],
            'born_date' => ['nullable', 'date'],
            'died_date' => ['required', 'date', Rule::when($request->filled('born_date'), 'after_or_equal:born_date')],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'type' => ['required', Rule::in([...array_keys(MemorialNotice::typeLabels()), MemorialNotice::FEATURED_OPTION])],
            'city' => ['nullable', 'string', 'max:120'],
            'place_id' => ['nullable', 'string', 'max:64'],
            'content' => ['nullable', 'string'],
            'condolence_url' => ['nullable', 'url:http,https', 'max:255'],
            'placer_first_name' => ['required', 'string', 'max:60'],
            'placer_last_name' => ['required', 'string', 'max:60'],
            'placer_email' => ['required', 'email', 'max:190'],
            'placer_phone' => ['nullable', 'string', 'max:60'],
            'create_account' => ['boolean'],
            'password' => ['exclude_unless:create_account,1', 'required', 'string', 'min:8', 'confirmed'],
            'billing_name' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:120'],
            'billing_company' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'nullable', 'string', 'max:120'],
            'billing_email' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'email', 'max:190'],
            'billing_address' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:190'],
            'billing_postcode' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:20'],
            'billing_city' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:120'],
        ]);

        $data['placer_name'] = trim($data['placer_first_name']).' '.trim($data['placer_last_name']);

        $place = $this->resolvePlace($geocoder, $data);
        if ($place === false) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'photo']))
                ->withErrors(['city' => 'Kies de woonplaats uit de lijst met suggesties.']);
        }

        $createsAccount = ! $request->user() && ($data['create_account'] ?? false);
        $owner = $this->resolveOwner($request, $data);
        if ($owner === null) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'photo']))
                ->withErrors(['placer_email' => 'Er bestaat al een account met dit e-mailadres. Log eerst in, dan koppelen we het bericht aan je account.']);
        }

        // Uitgelicht is een betaald overlijdensbericht: direct live, factuur volgt achteraf.
        $isFeatured = $data['type'] === MemorialNotice::FEATURED_OPTION;

        $displayName = trim(preg_replace('/\s+/', ' ', $data['deceased_name']));
        // De volledige naam van de overledene is de titel van het bericht.
        $title = $displayName;

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = Storage::url($request->file('photo')->store('notices/photos', 'public'));
        }

        $notice = MemorialNotice::query()->create([
            'user_id' => $owner->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'type' => $isFeatured ? MemorialNotice::TYPE_OVERLIJDENSBERICHT : $data['type'],
            // Eén veld voor de volledige naam (ook bij meerdere voornamen en tussenvoegsels);
            // de naamweergave plakt voor- en achternaam toch al aan elkaar.
            'deceased_first_name' => $displayName,
            'deceased_last_name' => null,
            'photo_url' => $photoUrl,
            'content' => NoticeContentSanitizer::clean($data['content'] ?? ''),
            'province' => $place['province'] ?? null,
            'city' => $place['name'] ?? null,
            'born_date' => $data['born_date'] ?? null,
            'died_date' => $data['died_date'],
            'status' => 'published',
            'is_featured' => $isFeatured,
            'billing_details' => $isFeatured ? [
                'name' => $data['billing_name'],
                'company' => $data['billing_company'] ?? null,
                'email' => $data['billing_email'],
                'address' => $data['billing_address'],
                'postcode' => $data['billing_postcode'],
                'city' => $data['billing_city'],
            ] : null,
            'condolence_url' => $data['condolence_url'] ?? null,
            'placer_name' => $data['placer_name'],
            'placer_email' => $data['placer_email'],
            'placer_phone' => $data['placer_phone'] ?? null,
            'published_at' => now(),
        ]);

        // Uitgelichte berichten hebben een eigen mail met factuurgegevens; zo krijgt de admin er maar één.
        foreach (User::adminEmails() as $adminEmail) {
            Mail::to($adminEmail)->send($isFeatured ? new AdminFeaturedNoticeMail($notice) : new AdminNewNoticeMail($notice));
        }

        if ($createsAccount) {
            Auth::login($owner);
            $request->session()->regenerate();

            return redirect()
                ->route('account.notices.index')
                ->with('status', 'Je bericht is geplaatst en je account is aangemaakt. Hier kun je het bericht later bewerken.');
        }

        return redirect()->route('home')->with('status', 'Bericht geplaatst.');
    }

    /**
     * Woonplaats en provincie komen uit de PDOK-woonplaats die in de suggestielijst is gekozen.
     * null = geen plaats opgegeven, false = geen geldige keuze uit de lijst.
     *
     * @return array{name: string, province: string|null}|false|null
     */
    private function resolvePlace(Geocoder $geocoder, array $data): array|false|null
    {
        $city = trim((string) ($data['city'] ?? ''));
        if ($city === '') {
            return null;
        }

        try {
            return $geocoder->placeById((string) ($data['place_id'] ?? '')) ?? false;
        } catch (Throwable $e) {
            // PDOK onbereikbaar: het bericht niet tegenhouden, de getypte plaats overnemen.
            Log::warning('Plaats controleren mislukt', ['place_id' => $data['place_id'] ?? null, 'error' => $e->getMessage()]);

            return ['name' => $city, 'province' => null];
        }
    }

    /**
     * Ingelogd: het eigen account. Account aangevraagd: een nieuw account (null als het e-mailadres al bestaat).
     * Anders het gedeelde account voor particuliere plaatsingen.
     */
    private function resolveOwner(Request $request, array $data): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        if (! ($data['create_account'] ?? false)) {
            return $this->resolvePublicOwner();
        }

        if (User::query()->where('email', $data['placer_email'])->exists()) {
            return null;
        }

        return User::query()->create([
            'name' => $data['placer_name'],
            'email' => $data['placer_email'],
            'password' => $data['password'],
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'bericht';
        $slug = $base;
        $counter = 2;

        while (MemorialNotice::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function resolvePublicOwner(): User
    {
        return User::query()->firstOrCreate(
            ['email' => 'particulier@overlijdens-berichten.nl'],
            ['name' => 'Particuliere Plaatsing', 'password' => bcrypt(Str::random(24))]
        );
    }
}
