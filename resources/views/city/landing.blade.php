@extends('layouts.public')

@section('title', $mode === 'crematorium' ? 'Crematorium in '.$cityName : 'Overlijdensberichten in '.$cityName)
@section('meta_description', $mode === 'crematorium'
    ? 'Zoek een crematorium in '.$cityName.' en bekijk recente overlijdensberichten, familieberichten en rouwadvertenties.'
    : 'Bekijk actuele overlijdensberichten in '.$cityName.'. Zoek op naam en lees familieberichten en rouwadvertenties uit de regio.')

@section('content')
    @include('partials.newsletter-alert-card', [
        'title' => 'Ontvang wekelijkse alerts voor '.$cityName,
        'region' => $cityName,
        'regionPlaceholder' => 'Stad of plaats',
    ])

    <div class="max-w-[1080px] mx-auto mt-14 flex flex-col lg:flex-row lg:items-start gap-4">
        <aside class="w-full lg:flex-1 lg:min-w-0 lg:sticky lg:top-4">
            <section class="card bg-base-100 border border-base-300">
                <div class="card-body gap-4">
                    <div>
                        @if($mode === 'crematorium')
                            <h1 class="card-title text-2xl">Crematorium in {{ $cityName }}</h1>
                        @else
                            <h1 class="card-title text-2xl">Overlijdensberichten in {{ $cityName }}</h1>
                        @endif
                        <p class="text-sm text-base-content/70">Zoek op naam of type bericht.</p>
                    </div>

                    @include('partials.notice-filters', [
                        'action' => $mode === 'crematorium' ? route('city.crematorium', ['city' => $city]) : route('city.show', ['city' => $city]),
                        'filters' => $filters,
                        'showLocation' => false,
                    ])
                </div>
            </section>
        </aside>

        <div class="w-full lg:w-[65%] lg:shrink-0 min-w-0 flex flex-col gap-4">
            @forelse($latestNotices as $notice)
                @php
                    $displayName = trim(($notice->deceased_first_name ?? '').' '.($notice->deceased_last_name ?? ''));
                    if ($displayName === '') {
                        $displayName = $notice->title;
                    }
                    $fallbackAvatar = asset('images/candle-placeholder.svg');
                @endphp

                <a href="{{ route('notice.show', $notice->slug) }}" class="card bg-base-100 border border-base-300 hover:bg-base-200/50 transition-colors">
                    <div class="card-body p-4 gap-3">
                        <div class="text-right text-sm italic text-base-content/70">
                            {{ $notice->city ?: ($notice->province ?: 'Onbekende locatie') }}
                            @isset($notice->distance_km)
                                <span class="not-italic">· {{ number_format($notice->distance_km, 0, ',', '.') }} km</span>
                            @endisset
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-20 rounded-full border border-base-300">
                                    @if($notice->photo_url)
                                        <img src="{{ $notice->photo_url }}" alt="Foto van {{ $displayName }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';" />
                                    @else
                                        <img src="{{ $fallbackAvatar }}" alt="Kaarsje ter nagedachtenis aan {{ $displayName }}" />
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
                <article class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        @if($filters->isActive())
                            Geen berichten gevonden die aan uw zoekopdracht voldoen.
                        @else
                            Er zijn nog geen berichten gepubliceerd.
                        @endif
                    </div>
                </article>
            @endforelse
        </div>
    </div>
@endsection
