{{-- Bericht in de lijst (homepage en stadspagina's); verwacht $notice. --}}
@php
    $displayName = trim(($notice->deceased_first_name ?? '').' '.($notice->deceased_last_name ?? ''));
    if ($displayName === '') {
        $displayName = $notice->title;
    }
    $fallbackAvatar = asset('images/candle-placeholder.svg');
@endphp

<a href="{{ route('notice.show', $notice->slug) }}" class="card bg-base-100 border border-base-300 hover:bg-base-200/50 hover:border-[#5a3a3a] focus-visible:border-[#5a3a3a] transition-colors">
    <div class="card-body px-4 py-3 flex-row items-center gap-3">
        <div class="avatar shrink-0">
            <div class="w-16 rounded-full border border-base-300">
                @if($notice->photo_url)
                    <img src="{{ $notice->photo_url }}" alt="Foto van {{ $displayName }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';" />
                @else
                    <img src="{{ $fallbackAvatar }}" alt="Kaarsje ter nagedachtenis aan {{ $displayName }}" />
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-baseline justify-between gap-3">
                <h2 class="font-semibold text-lg leading-tight break-words">{{ $displayName }}</h2>
                <span class="text-sm italic text-base-content/70 shrink-0">
                    {{ $notice->city ?: ($notice->province ?: 'Onbekende locatie') }}
                    @isset($notice->distance_km)
                        <span class="not-italic">· {{ number_format($notice->distance_km, 0, ',', '.') }} km</span>
                    @endisset
                </span>
            </div>
            <div class="text-sm text-base-content/70 mt-0.5">
                {{ $notice->born_date?->format('d-m-Y') ?: 'Onbekend' }}
                <span class="mx-1">|</span>
                {{ $notice->died_date?->format('d-m-Y') ?: 'Onbekend' }}
            </div>
            <p class="text-sm text-base-content/80 mt-1 line-clamp-2">{{ $notice->listExcerpt() }}</p>
        </div>
    </div>
</a>
