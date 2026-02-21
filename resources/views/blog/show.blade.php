@extends('layouts.public')

@section('title', $post->title)
@section('meta_description', $post->meta_description ?: 'Blog artikel')

@section('content')
    <article class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body">
            <div><a href="{{ route('blog.index') }}" class="btn btn-ghost btn-sm">&larr; Terug naar blog</a></div>
            <h1 class="text-2xl font-semibold">{{ $post->title }}</h1>
            <div class="prose max-w-none">{!! $post->content_html !!}</div>
        </div>
    </article>
@endsection
