<?php

namespace App\Http\Controllers;

use App\Mail\AdminFeaturedNoticeMail;
use App\Models\MemorialNotice;
use App\Models\User;
use App\Support\NoticeContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'excerpt' => ['nullable', 'string', 'max:600'],
            'content' => ['nullable', 'string'],
            'billing_name' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:120'],
            'billing_company' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'nullable', 'string', 'max:120'],
            'billing_email' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'email', 'max:190'],
            'billing_address' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:190'],
            'billing_postcode' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:20'],
            'billing_city' => ['exclude_unless:type,'.MemorialNotice::FEATURED_OPTION, 'required', 'string', 'max:120'],
        ]);

        // Uitgelicht is een betaald overlijdensbericht: direct live, factuur volgt achteraf.
        $isFeatured = $data['type'] === MemorialNotice::FEATURED_OPTION;

        $displayName = trim($data['deceased_first_name'].' '.($data['deceased_last_name'] ?? ''));
        $title = trim((string) ($data['title'] ?? '')) ?: $displayName;

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = Storage::url($request->file('photo')->store('notices/photos', 'public'));
        }

        $notice = MemorialNotice::query()->create([
            'user_id' => $this->resolvePublicOwner()->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'type' => $isFeatured ? MemorialNotice::TYPE_OVERLIJDENSBERICHT : $data['type'],
            'deceased_first_name' => $data['deceased_first_name'],
            'deceased_last_name' => $data['deceased_last_name'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
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
            'published_at' => now(),
        ]);

        if ($isFeatured) {
            foreach (User::adminEmails() as $adminEmail) {
                Mail::to($adminEmail)->send(new AdminFeaturedNoticeMail($notice));
            }
        }

        return redirect()->route('home')->with('status', 'Bericht geplaatst.');
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
