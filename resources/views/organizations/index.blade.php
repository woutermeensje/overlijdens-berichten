@extends('layouts.public')

@php
    $activeCategory = $filters['type'] ? $categories[$filters['type']] : null;
    $where = $filters['plaats'] ?: $filters['provincie'];
    $heading = trim(($activeCategory ?? 'Organisaties in de uitvaartbranche').($where ? ' in '.$where : ''));
    $isFiltered = $filters['type'] || $filters['provincie'] || $filters['plaats'] !== '';
@endphp

@section('title', $heading)
@section('meta_description', 'Vind uitvaartondernemers, crematoria, begraafplaatsen en andere organisaties uit de uitvaartbranche'.($where ? ' in '.$where : ' in Nederland').'. Filter op type en locatie.')
@if($organizations->currentPage() > 1 || $filters['plaats'] !== '')
    @section('meta_robots', 'noindex,follow')
@endif

@section('content')
    <section class="max-w-[1080px] mx-auto space-y-6">
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">{{ $heading }}</h1>
                <p class="text-base-content/70">Uitvaartondernemers, crematoria, begraafplaatsen en andere organisaties die nabestaanden helpen.</p>
            </div>
            <a href="{{ route('organizations.create') }}" class="btn btn-outline shrink-0">Organisatie toevoegen</a>
        </header>

        <form method="GET" action="{{ route('organizations.index') }}" class="card bg-base-100 border border-base-300">
            <div class="card-body py-4 grid grid-cols-1 md:grid-cols-[1fr_1fr_1fr_auto] gap-3 md:items-end">
                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Type</span>
                    <select name="type" class="select select-bordered w-full">
                        <option value="">Alle types</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Provincie</span>
                    <select name="provincie" class="select select-bordered w-full">
                        <option value="">Heel Nederland</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province }}" @selected($filters['provincie'] === $province)>{{ $province }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Plaats</span>
                    <input type="text" name="plaats" value="{{ $filters['plaats'] }}" class="input input-bordered w-full" placeholder="Bijvoorbeeld Utrecht">
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1 md:flex-none">Filteren</button>
                    @if($isFiltered)
                        <a href="{{ route('organizations.index') }}" class="btn btn-ghost">Wissen</a>
                    @endif
                </div>
            </div>
        </form>

        <p class="text-sm text-base-content/70">
            {{ $organizations->total() }} {{ $organizations->total() === 1 ? 'organisatie' : 'organisaties' }} gevonden
        </p>

        @if($organizations->isEmpty())
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body items-center text-center gap-3">
                    <p class="text-base-content/70">
                        {{ $isFiltered ? 'Geen organisaties gevonden voor deze filters.' : 'Er staan nog geen organisaties in het overzicht.' }}
                    </p>
                    <a href="{{ route('organizations.create') }}" class="btn btn-primary">Organisatie toevoegen</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($organizations as $organization)
                    @include('organizations._card')
                @endforeach
            </div>

            <div>{{ $organizations->links() }}</div>
        @endif
    </section>
@endsection
