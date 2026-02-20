<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountNoticeController extends Controller
{
    public function index(): View
    {
        $notices = auth()->user()->memorialNotices()->latest('created_at')->get();

        return view('account.notices.index', [
            'notices' => $notices,
        ]);
    }

    public function create(): View
    {
        return view('account.notices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateNotice($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = 'published';
        $data['published_at'] = now();

        auth()->user()->memorialNotices()->create($data);

        return redirect()
            ->route('account.notices.index')
            ->with('status', 'Bericht gepubliceerd.');
    }

    public function show(MemorialNotice $notice): RedirectResponse
    {
        $this->ensureOwner($notice);

        return redirect()->route('account.notices.edit', $notice);
    }

    public function edit(MemorialNotice $notice): View
    {
        $this->ensureOwner($notice);

        return view('account.notices.edit', [
            'notice' => $notice,
        ]);
    }

    public function update(Request $request, MemorialNotice $notice): RedirectResponse
    {
        $this->ensureOwner($notice);

        $data = $this->validateNotice($request);

        if ($notice->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $notice->id);
        }

        $notice->update($data);

        return redirect()
            ->route('account.notices.index')
            ->with('status', 'Bericht bijgewerkt.');
    }

    public function destroy(MemorialNotice $notice): RedirectResponse
    {
        $this->ensureOwner($notice);
        $notice->delete();

        return redirect()
            ->route('account.notices.index')
            ->with('status', 'Bericht verwijderd.');
    }

    private function validateNotice(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'type' => [
                'required',
                Rule::in([
                    MemorialNotice::TYPE_OVERLIJDENSBERICHT,
                    MemorialNotice::TYPE_FAMILIEBERICHT,
                    MemorialNotice::TYPE_ROUWADVERTENTIE,
                ]),
            ],
            'deceased_first_name' => ['nullable', 'string', 'max:120'],
            'deceased_last_name' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'photo_url' => ['nullable', 'url', 'max:255'],
            'content' => ['required', 'string'],
            'province' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'born_date' => ['nullable', 'date'],
            'died_date' => ['nullable', 'date'],
            'funeral_company_name' => ['nullable', 'string', 'max:190'],
            'funeral_company_contact' => ['nullable', 'string', 'max:190'],
            'funeral_company_phone' => ['nullable', 'string', 'max:60'],
            'funeral_company_email' => ['nullable', 'email', 'max:190'],
            'funeral_company_url' => ['nullable', 'url', 'max:255'],
            'next_of_kin_first_name' => ['nullable', 'string', 'max:120'],
            'next_of_kin_last_name' => ['nullable', 'string', 'max:120'],
            'next_of_kin_email' => ['nullable', 'email', 'max:190'],
            'condolence_url' => ['nullable', 'url', 'max:255'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base !== '' ? $base : 'bericht';
        $counter = 2;

        while (
            MemorialNotice::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function ensureOwner(MemorialNotice $notice): void
    {
        abort_unless($notice->user_id === auth()->id(), 403);
    }
}
