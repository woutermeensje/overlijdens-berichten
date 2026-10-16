@php
    $showLocation = $showLocation ?? true;
@endphp

<form method="get" action="{{ $action }}" class="flex flex-col gap-3">
    <label class="flex flex-col gap-1">
        <span class="text-sm font-medium">Naam</span>
        <input type="search" name="q" value="{{ $filters->q }}" placeholder="Naam of voornaam" class="input w-full" />
    </label>

    <label class="flex flex-col gap-1">
        <span class="text-sm font-medium">Type bericht</span>
        <select name="type" class="select w-full">
            <option value="">Alle types</option>
            @foreach(\App\Models\MemorialNotice::typeLabels() as $value => $label)
                <option value="{{ $value }}" @selected($filters->type === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    @if($showLocation)
        <div class="flex flex-col gap-1">
            <label for="filter-locatie" class="text-sm font-medium">Locatie</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    id="filter-locatie"
                    name="locatie"
                    value="{{ $filters->location }}"
                    placeholder="Plaats of postcode"
                    class="input w-full min-w-0"
                    list="filter-locatie-suggesties"
                    autocomplete="off"
                />
                <select name="straal" class="select w-28 shrink-0" aria-label="Straal">
                    @foreach(\App\Support\NoticeSearch::RADII as $radius)
                        <option value="{{ $radius }}" @selected($filters->radius === $radius)>+ {{ $radius }} km</option>
                    @endforeach
                </select>
            </div>
            <datalist id="filter-locatie-suggesties"></datalist>

            @if($filters->locationNotFound())
                <p class="text-sm text-warning">
                    "{{ $filters->location }}" konden we niet vinden; we zoeken nu op plaatsnaam.
                </p>
            @elseif($filters->origin)
                <p class="text-xs text-base-content/60">
                    Binnen {{ $filters->radius }} km van {{ $filters->origin['label'] }}
                </p>
            @endif
        </div>
    @endif

    <div class="flex gap-2 pt-1">
        <button type="submit" class="btn btn-primary flex-1">Zoeken</button>
        @if($filters->isActive())
            <a href="{{ $action }}" class="btn">Reset</a>
        @endif
    </div>
</form>

@if($showLocation)
    @once
        <script>
            (() => {
                const input = document.getElementById('filter-locatie');
                const list = document.getElementById('filter-locatie-suggesties');
                if (!input || !list) return;

                let timer;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    const term = input.value.trim();
                    if (term.length < 2) return;

                    timer = setTimeout(async () => {
                        try {
                            const params = new URLSearchParams({ q: term, fq: 'type:woonplaats', rows: 6, fl: 'weergavenaam' });
                            const res = await fetch('https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest?' + params);
                            const docs = (await res.json()).response?.docs ?? [];
                            list.replaceChildren(...docs.map(doc => {
                                const option = document.createElement('option');
                                option.value = doc.weergavenaam;
                                return option;
                            }));
                        } catch (e) {
                            // Suggesties zijn optioneel; zoeken werkt ook zonder.
                        }
                    }, 200);
                });
            })();
        </script>
    @endonce
@endif
