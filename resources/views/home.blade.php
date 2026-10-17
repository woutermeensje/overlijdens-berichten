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
                @include('notices._list-card')

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
