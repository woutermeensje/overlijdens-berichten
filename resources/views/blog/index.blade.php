@extends('layouts.public')

@section('title', 'Blog')

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm mb-4">
        <div class="card-body">
            <h1 class="card-title text-2xl">Blog</h1>
            <p class="text-base-content/70">Informatieve berichten over overlijden, uitvaart en praktische zaken.</p>
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($posts as $post)
            <article class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg leading-tight">{{ $post->title }}</h2>
                    <p class="text-base-content/70">{{ \Illuminate\Support\Str::limit(strip_tags($post->meta_description ?: $post->content_html), 150) }}</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-primary btn-sm">Lees artikel</a>
                    </div>
                </div>
            </article>
        @empty
            <article class="card bg-base-100 border border-base-300"><div class="card-body">Nog geen blogartikelen beschikbaar.</div></article>
        @endforelse
    </section>

    <div class="mt-4">{{ $posts->links() }}</div>
@endsection
