@extends('layouts.public')

@section('title', 'Actuele overlijdensberichten in Nederland')
@section('meta_description', 'Bekijk de meest recente overlijdensberichten, familieberichten en rouwadvertenties in Nederland. Zoek eenvoudig op naam en locatie.')

@section('structured_data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => (trim((string) config('app.name', '')) !== '' && trim((string) config('app.name', '')) !== 'Laravel')
                ? trim((string) config('app.name', ''))
                : 'overlijdens-berichten.nl',
            'url' => url('/'),
            'inLanguage' => 'nl-NL',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('home').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    @include('partials.newsletter-alert-card', [
        'region' => null,
        'brandAsHeading' => true,
    ])

    <div class="max-w-[1080px] mx-auto mt-14 flex flex-col lg:flex-row lg:items-start gap-4">
        <aside class="w-full lg:flex-1 lg:min-w-0 lg:sticky lg:top-4">
            <section class="card bg-base-100 border border-base-300">
                <div class="card-body gap-4">
                    <div>
                        <h2 class="card-title text-2xl">Filter</h2>
                        <p class="text-sm text-base-content/70">Zoek op naam, type bericht of in de buurt van een plaats.</p>
                    </div>

                    @include('partials.notice-filters', [
                        'action' => route('home'),
                        'filters' => $filters,
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
                            Geen berichten gevonden die aan je zoekopdracht voldoen.
                        @else
                            Er zijn nog geen berichten gepubliceerd.
                        @endif
                    </div>
                </article>
            @endforelse
        </div>
    </div>
@endsection
