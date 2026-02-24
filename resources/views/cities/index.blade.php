@extends('layouts.public')

@section('title', 'Alle steden')

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm mb-4">
        <div class="card-body">
            <h1 class="card-title text-2xl">Alle steden met overlijdensberichten</h1>
            <p class="text-base-content/70">Overzicht van steden met een eigen landingspagina.</p>
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($cities as $city)
            <article class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">{{ $city['name'] }}</h2>
                    <p class="text-base-content/70">{{ $city['count'] }} bericht{{ $city['count'] === 1 ? '' : 'en' }}</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('city.show', ['city' => $city['slug']]) }}" class="btn btn-primary btn-sm">Bekijk stadspagina</a>
                    </div>
                </div>
            </article>
        @empty
            <article class="card bg-base-100 border border-base-300"><div class="card-body">Er zijn nog geen steden met berichten gevonden.</div></article>
        @endforelse
    </section>
@endsection
