<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;

class PageController extends Controller
{
    public function show(string $path)
    {
        $normalized = trim($path, '/');

        $page = ContentPage::query()
            ->where('path', $normalized)
            ->where('is_active', true)
            ->firstOrFail();

        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
