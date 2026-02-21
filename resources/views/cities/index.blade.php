@extends('layouts.public')

@section('title', 'Alle steden')

@section('content')
    <section class="card" style="margin-bottom:14px;">
        <h1 style="margin:0 0 8px;">Alle steden met pagina's</h1>
        <p style="margin:0;color:#58656f;">Overzicht van steden waarvoor er pagina's beschikbaar zijn.</p>
    </section>

    <section class="grid grid-3">
        @forelse($cities as $city)
            <article class="card">
                <h2 style="margin:0 0 8px;">{{ $city['name'] }}</h2>
                <p style="margin:0 0 10px;color:#5a6772;">{{ $city['count'] }} pagina{{ $city['count'] === 1 ? '' : 's' }}</p>
                <a href="/{{ $city['example_path'] }}" style="text-decoration:none;color:#193a59;font-weight:600;">Bekijk voorbeeldpagina</a>
            </article>
        @empty
            <article class="card">Er zijn nog geen stadspagina's gevonden.</article>
        @endforelse
    </section>
@endsection
