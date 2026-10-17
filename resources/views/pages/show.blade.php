@extends('layouts.public')

@section('title', $page->title)
@section('meta_description', \Illuminate\Support\Str::limit(\Illuminate\Support\Str::squish(html_entity_decode(strip_tags($page->meta_description ?: $page->content_html))), 155, '…'))

@section('content')
    <article class="card bg-base-100 border border-base-300">
        <div class="card-body">
            <h1 class="text-2xl font-semibold">{{ $page->title }}</h1>
            <div class="prose max-w-none">{!! $page->content_html !!}</div>
        </div>
    </article>
@endsection
