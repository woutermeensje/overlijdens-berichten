<?php

namespace App\Http\Controllers;

use App\Mail\AdminFeaturedNoticeMail;
use App\Models\MemorialNotice;
use App\Models\User;
use App\Support\NoticeContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicNoticeWizardController extends Controller
{
    public function show()
    {
        return view('notice-wizard.form');
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'deceased_first_name' => ['required', 'string', 'max:120'],
            'deceased_last_name' => ['nullable', 'string', 'max:120'],
            'born_date' => ['nullable', 'date'],
            'died_date' => ['required', 'date', Rule::when($request->filled('born_date'), 'after_or_equal:born_date')],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'type' => ['required', Rule::in([...array_keys(MemorialNotice::typeLabels()), MemorialNotice::FEATURED_OPTION])],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:190'],
            'content' => ['nullable', 'string'],
            'placer_name' => ['required', 'string', 'max:120'],
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

        $createsAccount = ! $request->user() && ($data['create_account'] ?? false);
        $owner = $this->resolveOwner($request, $data);
        if ($owner === null) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'photo']))
                ->withErrors(['placer_email' => 'Er bestaat al een account met dit e-mailadres. Log eerst in, dan koppelen we het bericht aan uw account.']);
        }

        // Uitgelicht is een betaald overlijdensbericht: direct live, factuur volgt achteraf.
        $isFeatured = $data['type'] === MemorialNotice::FEATURED_OPTION;

        $displayName = trim($data['deceased_first_name'].' '.($data['deceased_last_name'] ?? ''));
        $title = trim((string) ($data['title'] ?? '')) ?: $displayName;

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = Storage::url($request->file('photo')->store('notices/photos', 'public'));
        }

        $notice = MemorialNotice::query()->create([
            'user_id' => $owner->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'type' => $isFeatured ? MemorialNotice::TYPE_OVERLIJDENSBERICHT : $data['type'],
            'deceased_first_name' => $data['deceased_first_name'],
            'deceased_last_name' => $data['deceased_last_name'] ?? null,
            'photo_url' => $photoUrl,
            'content' => NoticeContentSanitizer::clean($data['content'] ?? ''),
            'province' => $data['province'] ?? null,
            'city' => $data['city'] ?? null,
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
            'placer_name' => $data['placer_name'],
            'placer_email' => $data['placer_email'],
            'placer_phone' => $data['placer_phone'] ?? null,
            'published_at' => now(),
        ]);

        if ($isFeatured) {
            foreach (User::adminEmails() as $adminEmail) {
                Mail::to($adminEmail)->send(new AdminFeaturedNoticeMail($notice));
            }
        }

        if ($createsAccount) {
            Auth::login($owner);
            $request->session()->regenerate();

            return redirect()
                ->route('account.notices.index')
                ->with('status', 'Uw bericht is geplaatst en uw account is aangemaakt. Hier kunt u het bericht later bewerken.');
        }

        return redirect()->route('home')->with('status', 'Bericht geplaatst.');
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
