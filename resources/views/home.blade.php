@extends('layouts.public')

@section('title', 'Overlijdensberichten')

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm max-w-[1080px] mx-auto mb-4">
        <div class="card-body gap-4">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <h1 class="card-title text-2xl">Laatste overlijdensberichten</h1>
                    <p class="text-base-content/70">Zoek op naam, voornaam of locatie.</p>
                </div>
                <a href="{{ route('notice.place') }}" class="btn btn-primary">Overlijdensbericht plaatsen</a>
            </div>

            <form method="get" action="{{ route('home') }}" class="join w-full">
                <input type="search" name="q" value="{{ $search }}" placeholder="Zoek op naam, voornaam of locatie" class="input input-bordered join-item w-full" />
                <button type="submit" class="btn btn-primary join-item">Zoeken</button>
                @if($search !== '')
                    <a href="{{ route('home') }}" class="btn join-item">Reset</a>
                @endif
            </form>
        </div>
    </section>

    <section class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
