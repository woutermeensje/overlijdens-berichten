<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use App\Support\NoticeContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $latestNotices = MemorialNotice::query()
            ->published()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $like = '%'.$search.'%';

                    $q->where('title', 'like', $like)
                        ->orWhere('deceased_first_name', 'like', $like)
                        ->orWhere('deceased_last_name', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('province', 'like', $like);
                });
            })
            ->latest('published_at')
            ->latest('id')
            ->take(30)
            ->get();

        $totalNotices = MemorialNotice::query()->published()->count();

        return view('home', [
            'latestNotices' => $latestNotices,
            'search' => $search,
            'totalNotices' => $totalNotices,
        ]);
    }

    public function placeNotice()
    {
        return view('pages.place-notice');
    }

    public function showNotice(string $slug)
    {
        $notice = MemorialNotice::query()
            ->published()
            ->with(['condolences' => fn ($query) => $query->oldest('created_at')->oldest('id')])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('notices.show', [
            'notice' => $notice,
        ]);
    }

    public function storeCondolence(Request $request, string $slug): RedirectResponse
    {
        $notice = MemorialNotice::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $validator = validator($request->all(), [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'message' => ['required', 'string', 'min:3', 'max:4000'],
        ], [], [
            'first_name' => 'voornaam',
            'last_name' => 'achternaam',
            'email' => 'e-mailadres',
            'message' => 'bericht',
        ]);

        if ($validator->fails()) {
            return redirect(route('notice.show', $notice->slug).'#condoleance-form')
                ->withErrors($validator, 'condolence')
                ->withInput();
        }

        $validated = $validator->validated();
        $validated['message'] = NoticeContentSanitizer::clean($validated['message']);

        $notice->condolences()->create($validated);

        return redirect(route('notice.show', $notice->slug).'#condoleances')
            ->with('condolence_success', 'Bedankt, uw bericht is geplaatst.');
    }
}
