<?php

namespace App\Http\Controllers;

use App\Models\MemorialNotice;

class DashboardController extends Controller
{
    public const VIEW_PERIOD_DAYS = 60;

    public function index()
    {
        $notices = MemorialNotice::query()->latest('created_at')->get();

        // Vandaag telt mee, dus 60 dagen = vandaag + de 59 dagen ervoor.
        $since = now('Europe/Amsterdam')->subDays(self::VIEW_PERIOD_DAYS - 1)->toDateString();

        $inPeriod = fn ($query) => $query->where('date', '>=', $since);

        $noticeViews = MemorialNotice::query()
            ->whereHas('views', $inPeriod)
            ->withSum(['views as recent_views' => $inPeriod], 'views')
            ->orderByDesc('recent_views')
            ->orderBy('title')
            ->get();

        return view('account.dashboard', [
            'notices' => $notices,
            'noticeViews' => $noticeViews,
            'totalViews' => (int) $noticeViews->sum('recent_views'),
            'viewPeriodDays' => self::VIEW_PERIOD_DAYS,
        ]);
    }
}
