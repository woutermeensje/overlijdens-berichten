<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use App\Models\User;
use App\Support\NoticeContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'deceased_last_name' => ['required', 'string', 'max:120'],
            'born_date' => ['required', 'date'],
            'died_date' => ['required', 'date', 'after_or_equal:born_date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'type' => ['required', Rule::in(array_keys(MemorialNotice::typeLabels()))],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:190'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'content' => ['required', 'string'],
        ]);

        $displayName = trim($data['deceased_first_name'].' '.$data['deceased_last_name']);
        $title = trim((string) ($data['title'] ?? '')) ?: $displayName;

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = Storage::url($request->file('photo')->store('notices/photos', 'public'));
        }

        MemorialNotice::query()->create([
            'user_id' => $this->resolvePublicOwner()->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'type' => $data['type'],
            'deceased_first_name' => $data['deceased_first_name'],
            'deceased_last_name' => $data['deceased_last_name'],
            'excerpt' => $data['excerpt'] ?? null,
            'photo_url' => $photoUrl,
            'content' => NoticeContentSanitizer::clean($data['content']),
            'province' => $data['province'],
            'city' => $data['city'],
            'born_date' => $data['born_date'],
            'died_date' => $data['died_date'],
            'status' => 'published',
            'published_at' => now(),
        ]);

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
