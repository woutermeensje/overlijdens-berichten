<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notices = $user->isAdmin()
            ? \App\Models\MemorialNotice::query()->latest('created_at')->get()
            : $user->memorialNotices()->latest('created_at')->get();

        return view('account.dashboard', [
            'notices' => $notices,
        ]);
    }
}
