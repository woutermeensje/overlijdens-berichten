<?php

namespace App\Http\Controllers;

use App\Models\MemorialNoticeCondolence;
use Illuminate\Http\Request;

class CondolenceOverviewController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('zoek'));

        $condolences = MemorialNoticeCondolence::query()
            ->with('notice:id,title,slug,status')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn ($query) => $query
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('message', 'like', $like)
                    ->orWhereHas('notice', fn ($query) => $query->where('title', 'like', $like)));
            })
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('account.condolences.index', [
            'condolences' => $condolences,
            'search' => $search,
            'total' => MemorialNoticeCondolence::count(),
            'lastWeek' => MemorialNoticeCondolence::query()->where('created_at', '>=', now()->subWeek())->count(),
        ]);
    }

    public function destroy(MemorialNoticeCondolence $condolence)
    {
        $condolence->delete();

        return back()->with('status', 'De reactie is verwijderd.');
    }
}
