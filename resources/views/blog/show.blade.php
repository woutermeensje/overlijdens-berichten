@extends('layouts.public')

@section('title', $post->title)
@section('meta_description', $post->meta_description ?: 'Blog artikel')

@section('content')
    <article class="card">
        <a href="{{ route('blog.index') }}" style="text-decoration:none;color:#193a59;">&larr; Terug naar blog</a>
        <h1 style="margin:12px 0 12px;">{{ $post->title }}</h1>
        <div>{!! $post->content_html !!}</div>
    </article>
@endsection
