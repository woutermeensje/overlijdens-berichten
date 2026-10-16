@extends('layouts.account')

@section('title', 'Meldingen')

@php
    // Sequentiële schaal in één kleur: hoe meer aanmeldingen, hoe donkerder de provincie.
    $steps = [16, 32, 50, 70, 90];
    $fillFor = function (int $count) use ($steps, $maxPerProvince): string {
        if ($count === 0) {
            return 'var(--color-base-200)';
        }
        $step = $steps[min(count($steps), (int) ceil($count / $maxPerProvince * count($steps))) - 1];

        return "color-mix(in oklab, var(--color-primary) {$step}%, var(--color-base-100))";
    };
@endphp

@section('content')
    <h1 class="text-2xl font-semibold mb-2">Meldingen</h1>
    <p class="text-base-content/70 mb-4">Wie zich heeft aangemeld voor alerts over nieuwe overlijdensberichten, en uit welke regio.</p>

    <div class="stats stats-vertical lg:stats-horizontal w-full border border-base-300 mb-6">
        <div class="stat">
            <div class="stat-title">Aanmeldingen</div>
            <div class="stat-value text-primary">{{ $total }}</div>
            <div class="stat-desc">{{ $uniqueEmails }} unieke e-mailadressen</div>
        </div>
        <div class="stat">
            <div class="stat-title">Afgelopen 7 dagen</div>
            <div class="stat-value">{{ $lastWeek }}</div>
        </div>
        <div class="stat">
            <div class="stat-title">Heel Nederland</div>
            <div class="stat-value">{{ $nationwide }}</div>
            <div class="stat-desc">Aanmeldingen zonder plaats</div>
        </div>
    </div>

    <section class="border border-base-300 rounded-box bg-base-100 p-4 md:p-6 mb-6">
        <h2 class="text-lg font-semibold">Aanmeldingen per provincie</h2>
        <p class="text-sm text-base-content/70 mb-4">
            Op basis van de gekozen plaats.
            @if($withoutProvince > 0)
                {{ $withoutProvince }} {{ $withoutProvince === 1 ? 'aanmelding heeft' : 'aanmeldingen hebben' }} een plaats die niet te herleiden is en {{ $withoutProvince === 1 ? 'telt' : 'tellen' }} niet mee.
            @endif
        </p>

        <div class="flex flex-col lg:flex-row gap-6 lg:items-start">
            <div class="relative w-full lg:w-3/5 max-w-xl mx-auto" data-province-map>
                <svg viewBox="{{ $map['viewBox'] }}" class="w-full h-auto" role="img" aria-label="Kaart van Nederland met het aantal aanmeldingen per provincie">
                    @foreach($map['provinces'] as $name => $province)
                        <path d="{{ $province['d'] }}" fill="{{ $fillFor($provinces[$name]) }}"
                              stroke="var(--color-base-100)" stroke-width="2" stroke-linejoin="round"
                              class="cursor-pointer transition-[stroke] hover:stroke-[var(--color-base-content)]"
                              data-name="{{ $name }}" data-count="{{ $provinces[$name] }}">
                            <title>{{ $name }}: {{ $provinces[$name] }}</title>
                        </path>
                    @endforeach
                    @foreach($map['provinces'] as $name => $province)
                        <text x="{{ $province['label'][0] }}" y="{{ $province['label'][1] }}" text-anchor="middle" dominant-baseline="middle"
                              font-size="16" font-weight="600" fill="var(--color-base-content)"
                              stroke="var(--color-base-100)" stroke-width="4" paint-order="stroke" pointer-events="none">{{ $provinces[$name] }}</text>
                    @endforeach
                </svg>
                <div class="hidden absolute z-10 pointer-events-none rounded-box border border-base-300 bg-base-100 px-3 py-2 text-sm" data-map-tooltip></div>
            </div>

            <div class="w-full lg:w-2/5">
                <div class="flex items-center gap-2 text-xs text-base-content/70 mb-3" aria-hidden="true">
                    <span>Minder</span>
                    <span class="w-4 h-3 rounded-sm border border-base-300" style="background: var(--color-base-200)"></span>
                    @foreach($steps as $step)
                        <span class="w-4 h-3 rounded-sm" style="background: color-mix(in oklab, var(--color-primary) {{ $step }}%, var(--color-base-100))"></span>
                    @endforeach
                    <span>Meer</span>
                </div>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Provincie</th>
                            <th class="text-right">Aanmeldingen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($provinces as $name => $count)
                            <tr>
                                <td class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-sm shrink-0 border border-base-300" style="background: {{ $fillFor($count) }}"></span>
                                    {{ $name }}
                                </td>
                                <td class="text-right tabular-nums">{{ $count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <h2 class="text-lg font-semibold mb-3">Alle aanmeldingen</h2>
    <div class="overflow-x-auto border border-base-300 rounded-box bg-base-100">
        <table class="table">
            <thead>
                <tr>
                    <th>E-mailadres</th>
                    <th>Plaats</th>
                    <th>Provincie</th>
                    <th>Aangemeld op</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->email }}</td>
                        <td>{{ $subscription->region ?? 'Heel Nederland' }}</td>
                        <td>{{ $subscription->province ?? '—' }}</td>
                        <td class="whitespace-nowrap">{{ $subscription->created_at?->timezone('Europe/Amsterdam')->format('d-m-Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-base-content/70">Nog geen aanmeldingen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>

    <script>
        (() => {
            const map = document.querySelector('[data-province-map]');
            const tooltip = map.querySelector('[data-map-tooltip]');

            map.querySelectorAll('path[data-name]').forEach((path) => {
                path.addEventListener('mousemove', (event) => {
                    const count = Number(path.dataset.count);
                    tooltip.innerHTML = `<div class="font-semibold"></div><div class="text-base-content/70"></div>`;
                    tooltip.children[0].textContent = path.dataset.name;
                    tooltip.children[1].textContent = `${count} ${count === 1 ? 'aanmelding' : 'aanmeldingen'}`;
                    const box = map.getBoundingClientRect();
                    tooltip.style.left = `${event.clientX - box.left + 12}px`;
                    tooltip.style.top = `${event.clientY - box.top + 12}px`;
                    tooltip.classList.remove('hidden');
                });
                path.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
            });
        })();
    </script>
@endsection
