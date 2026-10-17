<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewOrganizationMail;
use App\Models\Organization;
use App\Models\RegionSubscription;
use App\Models\User;
use App\Services\Geocoder;
use App\Support\FormTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $category = array_key_exists((string) $request->query('type'), Organization::CATEGORIES) ? (string) $request->query('type') : null;
        $province = in_array($request->query('provincie'), RegionSubscription::PROVINCES, true) ? (string) $request->query('provincie') : null;
        $place = Str::limit(trim((string) $request->query('plaats')), 120, '');

        $organizations = Organization::query()
            ->published()
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($province, fn ($query) => $query->where('province', $province))
            ->when($place !== '', fn ($query) => $query->where('city', 'like', '%'.addcslashes($place, '%_\\').'%'))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('organizations.index', [
            'organizations' => $organizations,
            'categories' => Organization::CATEGORIES,
            'provinces' => RegionSubscription::PROVINCES,
            'filters' => ['type' => $category, 'provincie' => $province, 'plaats' => $place],
        ]);
    }

    public function create()
    {
        return view('organizations.create', ['categories' => Organization::CATEGORIES]);
    }

    public function store(Request $request, Geocoder $geocoder)
    {
        $timer = FormTimer::check($request);
        if ($timer === 'bot') {
            return back();
        }
        if ($timer === 'expired') {
            return back()->withInput()->withErrors(['form' => 'Het formulier is verlopen. Probeer het opnieuw.']);
        }

        // "www.voorbeeld.nl" ook accepteren: zonder protocol zetten we er https:// voor.
        $website = trim((string) $request->input('website'));
        if ($website !== '' && ! preg_match('#^https?://#i', $website)) {
            $request->merge(['website' => 'https://'.$website]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(array_keys(Organization::CATEGORIES))],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'address' => ['nullable', 'string', 'max:190'],
            'city' => ['required', 'string', 'max:120'],
            'place_id' => ['nullable', 'string', 'max:64'],
            'about' => ['required', 'string', 'min:20', 'max:3000'],
        ], [
            'name.required' => 'Vul de naam van je organisatie in.',
            'category.required' => 'Kies een categorie.',
            'category.in' => 'Kies een categorie.',
            'email.required' => 'Vul een e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'website.url' => 'Vul een geldige website in, bijvoorbeeld www.jouwbedrijf.nl.',
            'city.required' => 'Kies de vestigingsplaats.',
            'about.required' => 'Vertel iets over je organisatie.',
            'about.min' => 'Vertel iets meer over je organisatie (minimaal 20 tekens).',
            'about.max' => 'De tekst over je organisatie is te lang (maximaal 3000 tekens).',
        ]);

        try {
            $place = $geocoder->placeById((string) ($data['place_id'] ?? ''));
        } catch (Throwable $e) {
            // PDOK onbereikbaar: de aanmelding niet tegenhouden, de getypte plaats overnemen.
            Log::warning('Plaats controleren mislukt', ['place_id' => $data['place_id'] ?? null, 'error' => $e->getMessage()]);
            $place = ['id' => null, 'name' => Str::limit(trim($data['city']), 120, ''), 'province' => null];
        }

        if ($place === null) {
            return back()->withInput()->withErrors(['city' => 'Kies de vestigingsplaats uit de lijst met suggesties.']);
        }

        $organization = Organization::create([
            ...$data,
            'city' => $place['name'],
            'province' => $place['province'] ?? null,
            'place_id' => $place['id'],
            // Net als overlijdensberichten direct online; een beheerder kan hem offline halen.
            'status' => Organization::STATUS_PUBLISHED,
        ]);

        foreach (User::adminEmails() as $adminEmail) {
            Mail::to($adminEmail)->send(new AdminNewOrganizationMail($organization));
        }

        return redirect()->route('organizations.create')
            ->with('organization_success', 'Bedankt! '.$organization->name.' staat nu in het overzicht van organisaties.');
    }
}
