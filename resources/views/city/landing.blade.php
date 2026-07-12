@extends('layouts.public')

@section('title', $mode === 'crematorium' ? 'Crematorium in '.$cityName : 'Overlijdensberichten in '.$cityName)
@section('meta_description', $mode === 'crematorium'
    ? 'Zoek een crematorium in '.$cityName.' en bekijk recente overlijdensberichten, familieberichten en rouwadvertenties.'
    : 'Bekijk actuele overlijdensberichten in '.$cityName.'. Zoek op naam en lees familieberichten en rouwadvertenties uit de regio.')

@section('hero')
    @include('partials.location-hero', ['location' => $cityName, 'region' => $cityName])
@endsection

@section('content')
    @if(session('newsletter_success'))
        <div role="alert" class="alert alert-success max-w-[1080px] mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('newsletter_success') }}</span>
        </div>
    @endif
    @if(session('newsletter_error'))
        <div role="alert" class="alert alert-warning max-w-[1080px] mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>{{ session('newsletter_error') }}</span>
        </div>
    @endif

    <section class="card bg-base-100 border border-base-300 shadow-sm max-w-[1080px] mx-auto mb-4">
        <div class="card-body gap-4">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    @if($mode === 'crematorium')
                        <h1 class="card-title text-2xl">Crematorium in {{ $cityName }}</h1>
                        <p class="text-base-content/70">Overzichtspagina met de nieuwste overlijdensberichten.</p>
                    @else
                        <h1 class="card-title text-2xl">Overlijdensberichten in {{ $cityName }}</h1>
                        <p class="text-base-content/70">Zoek op naam, voornaam of locatie.</p>
                    @endif
                </div>
                <a href="{{ route('notice.wizard') }}" class="btn btn-primary">Bericht plaatsen</a>
            </div>

            <form method="get" action="{{ $mode === 'crematorium' ? route('city.crematorium', ['city' => $city]) : route('city.show', ['city' => $city]) }}" class="join w-full">
                <input type="search" name="q" value="{{ $search }}" placeholder="Zoek op naam, voornaam of locatie" class="input input-bordered join-item w-full" />
                <button type="submit" class="btn btn-primary join-item">Zoeken</button>
                @if($search !== '')
                    <a href="{{ $mode === 'crematorium' ? route('city.crematorium', ['city' => $city]) : route('city.show', ['city' => $city]) }}" class="btn join-item">Reset</a>
                @endif
            </form>
        </div>
    </section>

    <section class="max-w-[1080px] mx-auto grid grid-cols-1 gap-4">
        @forelse($latestNotices as $notice)
            @php
                $displayName = trim(($notice->deceased_first_name ?? '').' '.($notice->deceased_last_name ?? ''));
                if ($displayName === '') {
                    $displayName = $notice->title;
                }
                $fallbackAvatar = 'https://ui-avatars.com/api/?name='.urlencode($displayName).'&background=e2e7ec&color=6b7580&size=256';
            @endphp

            <a href="{{ route('notice.show', $notice->slug) }}" class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-shadow">
                <div class="card-body p-4 gap-3">
                    <div class="text-right text-sm italic text-base-content/70">
                        {{ $notice->city ?: ($notice->province ?: 'Onbekende locatie') }}
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-20 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                @if($notice->photo_url)
                                    <img src="{{ $notice->photo_url }}" alt="Foto van {{ $displayName }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';" />
                                @else
                                    <img src="{{ $fallbackAvatar }}" alt="Avatar van {{ $displayName }}" />
                                @endif
                            </div>
                        </div>

                        <div class="min-w-0">
                            <h2 class="font-semibold text-lg leading-tight break-words">{{ $displayName }}</h2>
                            <div class="text-sm text-base-content/70 mt-1">
                                {{ $notice->born_date?->format('d-m-Y') ?: 'Onbekend' }}
                                <span class="mx-1">|</span>
                                {{ $notice->died_date?->format('d-m-Y') ?: 'Onbekend' }}
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            @if($loop->iteration === 4)
                @include('partials.notice-list-ad')
            @endif
        @empty
            <article class="card bg-base-100 border border-base-300 max-w-[1080px] mx-auto">
                <div class="card-body">
                    @if($search !== '')
                        Geen berichten gevonden voor "{{ $search }}".
                    @else
                        Er zijn nog geen berichten gepubliceerd.
                    @endif
                </div>
            </article>
        @endforelse
    </section>
@endsection
