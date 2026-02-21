@extends('layouts.public')

@section('title', $page->title)
@section('meta_description', $page->meta_description ?: 'Pagina')

@section('content')
    <article class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body">
            <h1 class="text-2xl font-semibold">{{ $page->title }}</h1>
            <div class="prose max-w-none">{!! $page->content_html !!}</div>
        </div>
    </article>
@endsection
