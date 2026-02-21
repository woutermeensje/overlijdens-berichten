<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;
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

        return view('home', [
            'latestNotices' => $latestNotices,
            'search' => $search,
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
            ->where('slug', $slug)
            ->firstOrFail();

        return view('notices.show', [
            'notice' => $notice,
        ]);
    }
}
