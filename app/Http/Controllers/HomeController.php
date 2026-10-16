<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
use App\Services\Geocoder;
use App\Support\NoticeSearch;
use App\Support\NoticeContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, Geocoder $geocoder)
    {
        $filters = NoticeSearch::fromRequest($request, $geocoder);

        return view('home', [
            'latestNotices' => $filters->results(MemorialNotice::query()->published()),
            'filters' => $filters,
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
