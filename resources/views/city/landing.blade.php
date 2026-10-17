@extends('layouts.public')

@section('title', $mode === 'crematorium' ? 'Crematorium in '.$cityName : 'Overlijdensberichten in '.$cityName)
@section('meta_description', $mode === 'crematorium'
    ? 'Zoek een crematorium in '.$cityName.' en bekijk recente overlijdensberichten, familieberichten en rouwadvertenties.'
    : 'Bekijk actuele overlijdensberichten in '.$cityName.'. Zoek op naam en lees familieberichten en rouwadvertenties uit de regio.')

@section('content')
    @include('partials.newsletter-alert-card', [
        'region' => $cityName,
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
