@extends('layouts.public')

@php
    $where = $cityName ?? $province;
    $heading = $cityName ? 'Crematorium in '.$cityName : ($province ? 'Crematoria in '.$province : 'Crematoria in Nederland');
    $addUrl = route('organizations.create', ['categorie' => 'crematorium']);
@endphp

@section('title', $heading)
@section('meta_description', $cityName
    ? 'Zoek een crematorium in '.$cityName.'. Bekijk adres, website en contactgegevens van crematoria in '.$cityName.($province ? ' en de rest van '.$province : '').'.'
    : 'Overzicht van crematoria'.($province ? ' in '.$province : ' in Nederland').'. Filter op plaats of provincie en bekijk adres, website en contactgegevens.')
@if(! $cityName && $filters['plaats'] !== '')
    @section('meta_robots', 'noindex,follow')
@endif

@section('content')
    <section class="max-w-[1080px] mx-auto space-y-6">
        <header class="space-y-2">
            <nav class="text-sm text-base-content/60" aria-label="Kruimelpad">
                <a href="{{ route('crematoria.index') }}" class="link link-hover">Crematoria</a>
                @if($cityName)
                    <span aria-hidden="true">/</span> <span>{{ $cityName }}</span>
                @endif
            </nav>
            <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">{{ $heading }}</h1>
            <p class="text-base-content/70 max-w-2xl">
                @if($cityName)
                    Op zoek naar een crematorium in {{ $cityName }}? Hieronder vind je de crematoria in {{ $cityName }}{{ $province ? ' en de rest van '.$province : '' }}, met adres en contactgegevens.
                @else
                    Vind een crematorium bij jou in de buurt. Filter op plaats of provincie en bekijk adres en contactgegevens.
                @endif
            </p>
        </header>

        <form method="GET" action="{{ route('crematoria.index') }}" class="card bg-base-100 border border-base-300">
            <div class="card-body py-4 grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-3 md:items-end">
                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Plaats</span>
                    <input type="text" name="plaats" value="{{ $filters['plaats'] }}" class="input input-bordered w-full" placeholder="Bijvoorbeeld Utrecht">
                </label>
                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Provincie</span>
                    <select name="provincie" class="select select-bordered w-full">
                        <option value="">Heel Nederland</option>
                        @foreach($provinces as $option)
                            <option value="{{ $option }}" @selected($filters['provincie'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1 md:flex-none">Zoeken</button>
                    @if($cityName || $filters['provincie'] || $filters['plaats'] !== '')
                        <a href="{{ route('crematoria.index') }}" class="btn btn-ghost">Wissen</a>
                    @endif
                </div>
            </div>
        </form>

        <section class="space-y-3">
            @if($cityName)
                <h2 class="text-xl font-semibold">Crematoria in {{ $cityName }}</h2>
            @endif
            <p class="text-sm text-base-content/70">
                {{ $crematoria->count() }} {{ $crematoria->count() === 1 ? 'crematorium' : 'crematoria' }} gevonden
            </p>

            @if($crematoria->isEmpty())
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body items-center text-center gap-3">
                        <p class="text-base-content/70">
                            {{ $where ? 'We hebben nog geen crematorium in '.$where.' in ons overzicht.' : 'Er staan nog geen crematoria in ons overzicht.' }}
                        </p>
                        <a href="{{ $addUrl }}" class="btn btn-outline">Crematorium aanmelden</a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($crematoria as $organization)
                        @include('organizations._card')
                    @endforeach
                </div>
            @endif
        </section>

        @if($nearby->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-xl font-semibold">Andere crematoria in {{ $province }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($nearby as $organization)
                        @include('organizations._card')
                    @endforeach
                </div>
            </section>
        @endif

        <aside class="card bg-base-200/60 border border-base-300">
            <div class="card-body sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-semibold">Beheer je een crematorium?</h2>
                    <p class="text-sm text-base-content/70">Meld het gratis aan, zodat nabestaanden je makkelijk kunnen vinden.</p>
                </div>
                <a href="{{ $addUrl }}" class="btn btn-primary shrink-0">Crematorium aanmelden</a>
            </div>
        </aside>
    </section>
@endsection
