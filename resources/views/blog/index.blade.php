@extends('layouts.public')

@section('title', 'Blog')

@section('content')
    <section class="card" style="margin-bottom:14px;">
        <h1 style="margin:0 0 8px;">Blog</h1>
        <p style="margin:0;color:#58656f;">Informatieve berichten over overlijden, uitvaart en praktische zaken.</p>
    </section>

    <section class="grid grid-3">
        @forelse($posts as $post)
            <article class="card">
                <h2 style="font-size:20px;line-height:1.25;margin:0 0 8px;">
                    <a href="{{ route('blog.show', $post->slug) }}" style="color:inherit;text-decoration:none;">{{ $post->title }}</a>
                </h2>
                <p style="margin:0 0 12px;color:#53606c;">{{ \Illuminate\Support\Str::limit(strip_tags($post->meta_description ?: $post->content_html), 150) }}</p>
                <a href="{{ route('blog.show', $post->slug) }}" style="font-weight:600;color:#193a59;text-decoration:none;">Lees artikel</a>
            </article>
        @empty
            <article class="card">Nog geen blogartikelen beschikbaar.</article>
        @endforelse
    </section>

    <div style="margin-top:14px;">{{ $posts->links() }}</div>
@endsection
