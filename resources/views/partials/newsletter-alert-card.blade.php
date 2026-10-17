@php
    $region = $region ?? null;
    $description = $description ?? 'Meld u aan en ontvang elke week een overzicht van nieuwe berichten in uw mailbox.';
    // Alleen als hoofdkop gebruiken op pagina's die zelf geen h1 hebben.
    $brandHeading = ($brandAsHeading ?? false) ? 'h1' : 'span';
    $honeypotId = \Illuminate\Support\Str::slug($region ?: 'landelijk');
    $prefilledPlace = $region ? app(\App\Services\Geocoder::class)->findPlace($region) : null;
    $areaRows = array_values((array) old('areas', [
        $prefilledPlace
            ? ['type' => 'city', 'name' => $prefilledPlace['name'], 'id' => $prefilledPlace['id']]
            : ['type' => 'city', 'name' => '', 'id' => ''],
    ]));
@endphp

<div class="max-w-[1080px] mx-auto mb-6 h-6 flex items-center gap-2 text-sm text-base-content/70 min-w-0">
    <{{ $brandHeading }} class="font-semibold text-base-content shrink-0">Overlijdens-berichten.nl</{{ $brandHeading }}>
    <span aria-hidden="true" class="shrink-0">|</span>
    <p class="truncate">Alle overlijdensberichten, familieberichten en rouwadvertenties uit Nederland op één plek.</p>
</div>

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

    <div class="card bg-base-100 border border-base-300">
        <div class="card-body py-4 gap-3">
            <div class="flex items-start gap-3">
                <div class="text-primary mt-0.5 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <p class="flex-1 min-w-0 font-semibold text-base">{{ $description }}</p>
            </div>

            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col lg:flex-row lg:items-start gap-2" data-area-form>
                @csrf
                <input type="hidden" name="form_token" value="{{ \App\Http\Controllers\RegionSubscriptionController::formToken() }}">
                <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="hp_website_{{ $honeypotId }}">Website</label>
                    <input type="text" id="hp_website_{{ $honeypotId }}" name="website" value="" tabindex="-1" autocomplete="off">
                </div>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="uw@emailadres.nl" aria-label="E-mailadres" class="input input-bordered w-full lg:w-72 lg:shrink-0 px-3" />

                <div class="flex-1 min-w-0 space-y-2">
                    <div class="space-y-2" data-area-list>
                        @foreach($areaRows as $index => $area)
                            @include('partials.newsletter-area-row', ['index' => $index, 'area' => $area])
                        @endforeach
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <button type="button" class="btn btn-ghost btn-sm px-2" data-area-add>
                            <i class="ph ph-plus-circle text-lg" aria-hidden="true"></i> Nog een stad of provincie
                        </button>
                        @unless($region)
                            <span class="text-xs text-base-content/60">Laat leeg voor heel Nederland.</span>
                        @endunless
                    </div>
                </div>

                <button type="submit" class="btn btn-primary shrink-0">Alerts instellen</button>

                <template data-area-template>
                    @include('partials.newsletter-area-row', ['index' => '__INDEX__', 'area' => ['type' => 'city']])
                </template>
            </form>
        </div>
    </div>
</section>

@include('partials.place-picker-script')

@once
    <script>
        (() => {
            const MAX_AREAS = 10;

            document.querySelectorAll('[data-area-form]').forEach((form) => {
                const list = form.querySelector('[data-area-list]');
                const template = form.querySelector('[data-area-template]');
                const addButton = form.querySelector('[data-area-add]');
                let nextIndex = list.children.length;

                const refresh = () => {
                    const rows = list.querySelectorAll('[data-area-row]');
                    rows.forEach((row) => row.querySelector('[data-area-remove]').classList.toggle('invisible', rows.length === 1));
                    addButton.disabled = rows.length >= MAX_AREAS;
                };

                // Het invoerveld beweegt mee met de keuze: stad = plaatszoeker, provincie = keuzelijst.
                const setType = (row, type) => {
                    const city = row.querySelector('[data-area-city]');
                    const province = row.querySelector('[data-area-province]');
                    city.classList.toggle('hidden', type !== 'city');
                    province.classList.toggle('hidden', type !== 'province');
                    city.querySelectorAll('input').forEach((input) => input.disabled = type !== 'city');
                    province.querySelector('select').disabled = type !== 'province';
                    (type === 'city' ? city.querySelector('[data-place-input]') : province.querySelector('select')).focus();
                };

                const setup = (row) => {
                    window.initPlacePicker(row.querySelector('[data-place-picker]'));
                    row.querySelector('[data-area-type]').addEventListener('change', (event) => setType(row, event.target.value));
                    row.querySelector('[data-area-remove]').addEventListener('click', () => {
                        row.remove();
                        refresh();
                        list.querySelector('[data-area-row]:last-child [data-area-type]')?.focus();
                    });
                };

                addButton.addEventListener('click', () => {
                    const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                    list.insertAdjacentHTML('beforeend', html);
                    const row = list.lastElementChild;
                    setup(row);
                    refresh();
                    row.querySelector('[data-area-type]').focus();
                });

                list.querySelectorAll('[data-area-row]').forEach(setup);
                refresh();
            });
        })();
    </script>
@endonce
