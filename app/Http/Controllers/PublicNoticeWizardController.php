<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicNoticeWizardController extends Controller
{
    private const SESSION_KEY = 'public_notice_wizard';
    private const STEPS = [1, 2, 3];

    public function show(Request $request)
    {
        $requestedStep = (int) $request->query('step', 1);
        $step = in_array($requestedStep, self::STEPS, true) ? $requestedStep : 1;
        $data = $request->session()->get(self::SESSION_KEY, []);

        if (!$this->canAccessStep($step, $data)) {
            return redirect()->route('notice.wizard', ['step' => $this->firstAvailableStep($data)]);
        }

        return view('notice-wizard.form', [
            'step' => $step,
            'data' => $data,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $step = (int) $request->input('step', 1);
        if (!in_array($step, self::STEPS, true)) {
            $step = 1;
        }

        $data = $request->session()->get(self::SESSION_KEY, []);
        if (!$this->canAccessStep($step, $data)) {
            return redirect()->route('notice.wizard', ['step' => $this->firstAvailableStep($data)]);
        }

        $action = (string) $request->input('action', 'next');
        if ($action === 'back') {
            return redirect()->route('notice.wizard', ['step' => max(1, $step - 1)]);
        }

        if ($step === 1) {
            $validated = $request->validate([
                'deceased_first_name' => ['required', 'string', 'max:120'],
                'deceased_last_name' => ['required', 'string', 'max:120'],
                'born_date' => ['required', 'date'],
                'died_date' => ['required', 'date', 'after_or_equal:born_date'],
                'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            if ($request->hasFile('photo')) {
                if (!empty($data['photo_path']) && Storage::disk('public')->exists($data['photo_path'])) {
                    Storage::disk('public')->delete($data['photo_path']);
                }

                $photoPath = $request->file('photo')->store('notices/photos', 'public');
                $validated['photo_path'] = $photoPath;
                $validated['photo_url'] = Storage::url($photoPath);
            }

            unset($validated['photo']);
            $request->session()->put(self::SESSION_KEY, array_merge($data, $validated));

            return redirect()->route('notice.wizard', ['step' => 2]);
        }

        if ($step === 2) {
            $validated = $request->validate([
                'type' => [
                    'required',
                    Rule::in([
                        MemorialNotice::TYPE_OVERLIJDENSBERICHT,
                        MemorialNotice::TYPE_FAMILIEBERICHT,
                        MemorialNotice::TYPE_ROUWADVERTENTIE,
                    ]),
                ],
                'city' => ['required', 'string', 'max:120'],
                'province' => ['required', 'string', 'max:120'],
                'title' => ['nullable', 'string', 'max:190'],
                'excerpt' => ['nullable', 'string', 'max:600'],
                'content' => ['required', 'string'],
            ]);

            $request->session()->put(self::SESSION_KEY, array_merge($data, $validated));
            return redirect()->route('notice.wizard', ['step' => 3]);
        }

        $data = $request->session()->get(self::SESSION_KEY, []);
        if (!$this->canAccessStep(3, $data)) {
            return redirect()->route('notice.wizard', ['step' => $this->firstAvailableStep($data)]);
        }

        $displayName = trim(($data['deceased_first_name'] ?? '').' '.($data['deceased_last_name'] ?? ''));
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = $displayName;
        }

        $slug = $this->uniqueSlug($title);

        MemorialNotice::query()->create([
            'user_id' => $this->resolvePublicOwner()->id,
            'title' => $title,
            'slug' => $slug,
            'type' => $data['type'],
            'deceased_first_name' => $data['deceased_first_name'],
            'deceased_last_name' => $data['deceased_last_name'],
            'excerpt' => $data['excerpt'] ?? null,
            'photo_url' => $data['photo_url'] ?? null,
            'content' => $data['content'],
            'province' => $data['province'],
            'city' => $data['city'],
            'born_date' => $data['born_date'],
            'died_date' => $data['died_date'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('home')->with('status', 'Bericht geplaatst.');
    }

    private function canAccessStep(int $step, array $data): bool
    {
        if ($step <= 1) {
            return true;
        }

        if (empty($data['deceased_first_name']) || empty($data['deceased_last_name']) || empty($data['born_date']) || empty($data['died_date'])) {
            return false;
        }

        if ($step === 2) {
            return true;
        }

        return !empty($data['type']) && !empty($data['city']) && !empty($data['province']) && !empty($data['content']);
    }

    private function firstAvailableStep(array $data): int
    {
        if ($this->canAccessStep(3, $data)) {
            return 3;
        }

        if ($this->canAccessStep(2, $data)) {
            return 2;
        }

        return 1;
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
