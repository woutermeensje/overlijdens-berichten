@extends('layouts.public')

@php
    $seoDisplayName = trim(($notice->deceased_first_name ?? '').' '.($notice->deceased_last_name ?? ''));
    if ($seoDisplayName === '') {
        $seoDisplayName = $notice->title;
    }
@endphp

@section('title', $seoDisplayName.' - overlijdensbericht'.($notice->city ? ' in '.$notice->city : ''))
@section('meta_description', \Illuminate\Support\Str::limit(($notice->excerpt ? strip_tags($notice->excerpt).' ' : '').'Lees het overlijdensbericht van '.$seoDisplayName.($notice->city ? ' uit '.$notice->city : '').'.', 155))
@section('og_type', 'article')

@section('structured_data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $notice->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags($notice->excerpt ?: $notice->content), 155),
            'datePublished' => optional($notice->published_at)->toAtomString(),
            'dateModified' => optional($notice->updated_at)->toAtomString(),
            'mainEntityOfPage' => url()->current(),
            'inLanguage' => 'nl-NL',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    @php
        $displayName = $seoDisplayName;
        $fallbackAvatar = 'https://ui-avatars.com/api/?name='.urlencode($displayName).'&background=e2e7ec&color=6b7580&size=256';
    @endphp

    <article class="card bg-base-100 border border-base-300 shadow-sm max-w-[1080px] mx-auto">
        <div class="card-body gap-4">
            <div><a href="{{ route('home') }}" class="btn btn-ghost btn-sm">&larr; Terug naar overzicht</a></div>

            <div class="flex flex-col md:flex-row gap-4 md:items-center">
                <div class="avatar">
                    <div class="w-28 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                        @if($notice->photo_url)
                            <img src="{{ $notice->photo_url }}" alt="Foto van {{ $displayName }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';" />
                        @else
                            <img src="{{ $fallbackAvatar }}" alt="Avatar van {{ $displayName }}" />
                        @endif
                    </div>
                </div>

                <div>
                    <h1 class="text-3xl font-semibold">{{ $displayName }}</h1>
                    <div class="text-base-content/70 mt-2 flex flex-wrap gap-3">
                        <span><i class="ph ph-calendar-blank"></i> {{ $notice->born_date?->format('d-m-Y') ?: 'Onbekend' }} - {{ $notice->died_date?->format('d-m-Y') ?: 'Onbekend' }}</span>
                        <span><i class="ph ph-map-pin"></i> {{ $notice->city ?: 'Onbekend' }}@if($notice->province), {{ $notice->province }}@endif</span>
                    </div>
                </div>
            </div>

            <div class="divider my-0"></div>
            <div class="prose max-w-none">{!! $notice->content !!}</div>
        </div>
    </article>
@endsection
