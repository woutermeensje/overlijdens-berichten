@php
    $region = $region ?? null;
    $title = $title ?? 'Ontvang wekelijkse alerts';
    $description = $description ?? 'Meld u aan en ontvang elke week een overzicht van nieuwe berichten in uw mailbox.';
    $regionPlaceholder = $regionPlaceholder ?? 'Stad of plaats (optioneel)';
    $honeypotId = \Illuminate\Support\Str::slug($region ?: 'landelijk');
@endphp

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
                    <p class="font-semibold text-base">{{ $title }}</p>
                    <p class="text-sm text-base-content/70">{{ $description }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="hp_website_{{ $honeypotId }}">Website</label>
                    <input type="text" id="hp_website_{{ $honeypotId }}" name="website" value="" tabindex="-1" autocomplete="off">
                </div>
                <input type="email" name="email" required placeholder="uw@emailadres.nl" class="input input-bordered flex-1 w-full" />
                <input type="text" name="region" value="{{ $region }}" placeholder="{{ $regionPlaceholder }}" class="input input-bordered flex-1 w-full" />
                <button type="submit" class="btn btn-primary shrink-0">Alerts instellen</button>
            </form>
        </div>
    </div>
</section>
