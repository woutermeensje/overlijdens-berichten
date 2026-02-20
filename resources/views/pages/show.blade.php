@extends('layouts.public')

@section('title', $page->title)
@section('meta_description', $page->meta_description ?: 'Pagina')

@section('content')
    <article class="card">
        <h1 style="margin-top:0;">{{ $page->title }}</h1>
        <div>{!! $page->content_html !!}</div>
    </article>
@endsection
