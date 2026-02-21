<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;

class BlogController extends Controller
{
    public function index()
    {
        $posts = ContentPage::query()
            ->where('is_active', true)
            ->where('content_type', 'blog')
            ->orderByDesc('id')
            ->paginate(18);

        return view('blog.index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $slug)
    {
        $post = ContentPage::query()
            ->where('is_active', true)
            ->where('content_type', 'blog')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', [
            'post' => $post,
        ]);
    }
}
