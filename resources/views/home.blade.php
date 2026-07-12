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

@section('hero')
    <section class="text-white" style="background: linear-gradient(135deg, #3d2626 0%, #5a3a3a 55%, #4a2f2f 100%);">
        <div class="max-w-6xl mx-auto w-full px-4 py-14 md:py-20">
            <p class="text-xs md:text-sm font-bold tracking-widest uppercase">overlijdens-berichten.nl</p>
            <div class="h-0.5 w-10 mt-2 mb-5" style="background-color:#c9a876;"></div>

            <h1 class="text-3xl md:text-5xl font-bold leading-tight max-w-3xl mb-4">
                Overlijdensberichten en rouwadvertenties uit heel Nederland.
            </h1>

            <p class="text-white/80 text-base md:text-lg max-w-2xl">
                Bekijk <span class="font-semibold" style="color:#c9a876;">{{ number_format($totalNotices, 0, ',', '.') }}</span> overlijdensberichten of
                <a href="{{ route('notice.wizard') }}" class="font-semibold underline hover:no-underline" style="color:#c9a876;">plaats direct een overlijdensbericht</a>.
                Meld u ook aan voor onze <a href="#nieuwsbrief" class="font-semibold underline hover:no-underline" style="color:#c9a876;">nieuwsbrief</a>.
            </p>
        </div>
    </section>
@endsection

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm max-w-[1080px] mx-auto mb-4">
        <div class="card-body gap-4">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <h1 class="card-title text-2xl">Laatste overlijdensberichten</h1>
                    <p class="text-base-content/70">Zoek op naam, voornaam of locatie.</p>
                </div>
                <a href="{{ route('notice.wizard') }}" class="btn btn-primary">Bericht plaatsen</a>
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

    <section id="nieuwsbrief" class="max-w-[1080px] mx-auto mb-4">
        @if(session('newsletter_success'))
            <div role="alert" class="alert alert-success mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('newsletter_success') }}</span>
            </div>
        @endif
        @if(session('newsletter_error'))
            <div role="alert" class="alert alert-warning mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('newsletter_error') }}</span>
            </div>
        @endif
        <div class="card bg-base-100 border border-primary/30 shadow-sm">
            <div class="card-body py-4 gap-3">
                <div class="flex items-start gap-3">
                    <div class="text-primary mt-0.5 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-base">Ontvang wekelijks de laatste overlijdensberichten</p>
                        <p class="text-sm text-base-content/70">Meld u aan en ontvang elke week een overzicht van nieuwe berichten in uw mailbox.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col sm:flex-row gap-2">
                    @csrf
                    <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                        <label for="hp_website">Website</label>
                        <input type="text" id="hp_website" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="email" name="email" required placeholder="uw@emailadres.nl" class="input input-bordered flex-1 w-full" />
                    <input type="text" name="region" placeholder="Stad of plaats (optioneel)" class="input input-bordered flex-1 w-full" />
                    <button type="submit" class="btn btn-primary shrink-0">Aanmelden</button>
                </form>
            </div>
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
