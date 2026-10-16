<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $notices = \App\Models\MemorialNotice::query()->latest('created_at')->get();

        return view('account.dashboard', [
            'notices' => $notices,
        ]);
    }
}
