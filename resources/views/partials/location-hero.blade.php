@php
    $location = $location ?? 'heel Nederland';
    $region = $region ?? null;
    $honeypotId = \Illuminate\Support\Str::slug($region ?: 'landelijk');
@endphp

<section class="overflow-hidden" style="background:#ffffff;color:#333;">
    <div class="max-w-6xl mx-auto w-full px-4 h-[300px] max-h-[300px] flex flex-col justify-center">
        <h1 class="text-3xl md:text-5xl font-bold leading-tight max-w-3xl mb-4">
            Overlijdensberichten en rouwadvertenties uit <span class="pb-1" style="border-bottom: 4px solid #3d2626;">{{ $location }}</span>.
        </h1>
    </div>
</section>

<section id="nieuwsbrief" class="w-full overflow-hidden" style="background:#5a3a3a;color:#ffffff;">
    <div class="max-w-6xl mx-auto w-full px-4 h-[100px] max-h-[100px] overflow-hidden flex items-center gap-3">
        <div class="shrink-0 text-white/90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>

        <div class="hidden sm:block shrink-0 min-w-0 sm:w-56 md:w-72 lg:w-80">
            <p class="font-semibold text-sm md:text-base leading-tight truncate">Ontvang wekelijkse alerts</p>
            <p class="hidden lg:block text-sm text-white/75 leading-tight truncate">De nieuwste overlijdensberichten in uw mailbox.</p>
        </div>

        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex min-w-0 flex-1 items-center gap-2">
            @csrf
            <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                <label for="hp_website_{{ $honeypotId }}">Website</label>
                <input type="text" id="hp_website_{{ $honeypotId }}" name="website" value="" tabindex="-1" autocomplete="off">
            </div>
            <input type="email" name="email" required placeholder="uw@emailadres.nl" class="input input-sm input-bordered h-10 min-h-10 flex-1 min-w-0 bg-white text-base-content" />
            <input type="text" name="region" value="{{ $region }}" placeholder="Stad of plaats" class="hidden md:block input input-sm input-bordered h-10 min-h-10 flex-1 min-w-0 bg-white text-base-content" />
            <button type="submit" class="btn btn-sm h-10 min-h-10 shrink-0 border-white bg-white px-4 font-semibold" style="color:#5a3a3a;">Aanmelden</button>
        </form>
    </div>
</section>
