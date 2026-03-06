@extends('layouts.public')

@section('title', $post->title)
@section('meta_description', $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($post->content_html), 155, '...'))
@section('og_type', 'article')

@section('structured_data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($post->content_html), 155, '...'),
            'mainEntityOfPage' => url()->current(),
            'dateModified' => optional($post->updated_at)->toAtomString(),
            'inLanguage' => 'nl-NL',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    <article class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body">
            <div><a href="{{ route('blog.index') }}" class="btn btn-ghost btn-sm">&larr; Terug naar blog</a></div>
            <h1 class="text-2xl font-semibold">{{ $post->title }}</h1>
            <div class="prose max-w-none">{!! $post->content_html !!}</div>
        </div>
    </article>
@endsection
