@extends('layouts.public')

@section('title', 'Contact')
@section('meta_description', 'Heb je een vraag over een overlijdensbericht, familiebericht of rouwadvertentie? Neem contact op met overlijdens-berichten.nl per e-mail of telefoon.')

@php
    $email = config('app.contact_email');
    $phone = config('app.contact_phone');
    $phoneHref = '+31'.ltrim(preg_replace('/\D/', '', $phone), '0');

    // Knoppen in de onderwerpblokken scrollen naar het formulier en kiezen daar het onderwerp.
    $topics = [
        [
            'icon' => 'ph-note-pencil',
            'title' => 'Een bericht plaatsen',
            'text' => 'Plaats zelf gratis een overlijdensbericht, familiebericht of rouwadvertentie. Het staat direct online.',
            'link' => ['label' => 'Bericht plaatsen', 'href' => route('notice.wizard')],
        ],
        [
            'icon' => 'ph-pencil-simple-line',
            'title' => 'Een bericht wijzigen of offline halen',
            'text' => 'Heb je bij het plaatsen een account aangemaakt? Dan kun je het bericht zelf aanpassen of offline halen. Anders helpen we je graag.',
            'link' => ['label' => 'Wijziging doorgeven', 'subject' => 'wijzigen'],
        ],
        [
            'icon' => 'ph-star',
            'title' => 'Uitgelicht bericht en facturen',
            'text' => 'Vragen over een uitgelicht overlijdensbericht of over je factuur.',
            'link' => ['label' => 'Stel je vraag', 'subject' => 'factuur'],
        ],
        [
            'icon' => 'ph-buildings',
            'title' => 'Voor uitvaartondernemers',
            'text' => 'Plaats je regelmatig berichten namens families? We maken graag afspraken die passen bij jouw onderneming.',
            'link' => ['label' => 'Neem contact op', 'subject' => 'uitvaartondernemer'],
        ],
        [
            'icon' => 'ph-bell-simple-slash',
            'title' => 'Alerts afmelden',
            'text' => 'Onderaan elke alert-mail staat een link om je direct af te melden. Lukt dat niet, laat het ons dan weten.',
            'link' => ['label' => 'Afmelden lukt niet', 'subject' => 'alerts'],
        ],
        [
            'icon' => 'ph-chat-circle-text',
            'title' => 'Andere vraag',
            'text' => 'Staat je vraag er niet tussen? Stel hem gerust via het contactformulier.',
            'link' => ['label' => 'Stel je vraag', 'subject' => 'vraag'],
        ],
    ];

    $selectedSubject = old('subject', array_key_exists((string) request('onderwerp'), $subjects) ? request('onderwerp') : 'vraag');
@endphp

@section('structured_data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => 'Contact',
            'url' => route('contact'),
            'mainEntity' => [
                '@type' => 'Organization',
                'name' => 'overlijdens-berichten.nl',
                'url' => url('/'),
                'email' => $email,
                'telephone' => $phoneHref,
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'contactType' => 'customer service',
                    'email' => $email,
                    'telephone' => $phoneHref,
                    'areaServed' => 'NL',
                    'availableLanguage' => 'Dutch',
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    <section class="max-w-[1080px] mx-auto space-y-6">
        <header class="space-y-2 max-w-2xl">
            <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">Contact</h1>
            <p class="text-base-content/70">
                Heb je een vraag over een overlijdensbericht, familiebericht of rouwadvertentie? Wil je een bericht laten aanpassen
                of juist offline laten halen? Neem gerust contact met ons op.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="mailto:{{ $email }}" class="card bg-base-100 border border-base-300 hover:border-primary transition-colors">
                <div class="card-body flex-row items-center gap-4">
                    <span class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center shrink-0">
                        <i class="ph ph-envelope-simple text-2xl text-primary" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm text-base-content/60">E-mail</span>
                        <span class="block text-lg font-semibold break-all">{{ $email }}</span>
                    </span>
                </div>
            </a>
            <a href="tel:{{ $phoneHref }}" class="card bg-base-100 border border-base-300 hover:border-primary transition-colors">
                <div class="card-body flex-row items-center gap-4">
                    <span class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center shrink-0">
                        <i class="ph ph-phone text-2xl text-primary" aria-hidden="true"></i>
                    </span>
                    <span>
                        <span class="block text-sm text-base-content/60">Telefoon</span>
                        <span class="block text-lg font-semibold">{{ $phone }}</span>
                    </span>
                </div>
            </a>
        </div>

        <section class="space-y-3">
            <h2 class="text-xl font-semibold">Waarmee kunnen we je helpen?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($topics as $topic)
                    <article class="card bg-base-100 border border-base-300">
                        <div class="card-body gap-3">
                            <h3 class="flex items-center gap-2 font-semibold">
                                <i class="ph {{ $topic['icon'] }} text-xl text-primary" aria-hidden="true"></i>
                                {{ $topic['title'] }}
                            </h3>
                            <p class="text-sm text-base-content/70 flex-1">{{ $topic['text'] }}</p>
                            @isset($topic['link']['subject'])
                                <a href="{{ route('contact', ['onderwerp' => $topic['link']['subject']]) }}#contactformulier" data-contact-subject="{{ $topic['link']['subject'] }}"
                                   class="link link-hover text-sm font-medium inline-flex items-center gap-1">
                                    {{ $topic['link']['label'] }} <i class="ph ph-arrow-down" aria-hidden="true"></i>
                                </a>
                            @else
                                <a href="{{ $topic['link']['href'] }}" class="link link-hover text-sm font-medium inline-flex items-center gap-1">
                                    {{ $topic['link']['label'] }} <i class="ph ph-arrow-right" aria-hidden="true"></i>
                                </a>
                            @endisset
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="contactformulier" class="card bg-base-100 border border-base-300 scroll-mt-6">
            <div class="card-body gap-5">
                <div class="space-y-1">
                    <h2 class="text-xl font-semibold">Stel je vraag</h2>
                    <p class="text-sm text-base-content/70">
                        Vul het formulier in, dan nemen we zo snel mogelijk contact met je op. Gaat je vraag over een specifiek bericht?
                        Vermeld dan de naam van de overledene of de link naar het bericht.
                    </p>
                </div>

                @if(session('contact_success'))
                    <div role="alert" class="alert alert-success">
                        <i class="ph ph-check-circle text-xl" aria-hidden="true"></i>
                        <span>{{ session('contact_success') }}</span>
                    </div>
                @endif
                @if(session('contact_error'))
                    <div role="alert" class="alert alert-warning">
                        <span>{{ session('contact_error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div role="alert" class="alert alert-error">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf
                    {!! \App\Support\FormTimer::field() !!}
                    <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                        <label for="hp_contact">Website</label>
                        <input type="text" id="hp_contact" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Naam <span class="text-error" aria-hidden="true">*</span></span>
                            <input class="input input-bordered w-full" name="name" value="{{ old('name') }}" autocomplete="name" required data-contact-first-field>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">E-mailadres <span class="text-error" aria-hidden="true">*</span></span>
                            <input type="email" class="input input-bordered w-full" name="email" value="{{ old('email') }}" autocomplete="email" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Telefoonnummer <span class="font-normal text-base-content/60">(optioneel)</span></span>
                            <input type="tel" class="input input-bordered w-full" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Onderwerp <span class="text-error" aria-hidden="true">*</span></span>
                            <select class="select select-bordered w-full" name="subject" required data-contact-subject-select>
                                @foreach($subjects as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedSubject === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Over welk bericht gaat het? <span class="font-normal text-base-content/60">(optioneel)</span></span>
                        <input class="input input-bordered w-full" name="notice" value="{{ old('notice') }}" placeholder="Naam van de overledene of link naar het bericht" autocomplete="off">
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Je vraag of bericht <span class="text-error" aria-hidden="true">*</span></span>
                        <textarea class="textarea textarea-bordered w-full" name="message" rows="6" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>
                    </label>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-xs text-base-content/60">Velden met <span class="text-error">*</span> zijn verplicht. We gebruiken je gegevens alleen om je vraag te beantwoorden.</p>
                        <button type="submit" class="btn btn-primary">Verstuur</button>
                    </div>
                </form>
            </div>
        </section>
    </section>

    <script>
        (() => {
            const form = document.getElementById('contactformulier');
            const select = form.querySelector('[data-contact-subject-select]');

            // Onderwerpknoppen: onderwerp kiezen en soepel naar het formulier scrollen, zonder de pagina te herladen.
            document.querySelectorAll('[data-contact-subject]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    select.value = link.dataset.contactSubject;
                    form.scrollIntoView({behavior: 'smooth', block: 'start'});
                    form.querySelector('[data-contact-first-field]').focus({preventScroll: true});
                    history.replaceState(null, '', link.href);
                });
            });
        })();
    </script>
@endsection
