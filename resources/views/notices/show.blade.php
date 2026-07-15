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
        $hasFuneralInfo = collect([
            $notice->funeral_company_name,
            $notice->funeral_company_contact,
            $notice->funeral_company_phone,
            $notice->funeral_company_email,
            $notice->funeral_company_url,
        ])->filter(fn ($value) => filled($value))->isNotEmpty();
    @endphp

    <div class="max-w-[1080px] mx-auto space-y-4">
        <div><a href="{{ route('home') }}" class="btn btn-ghost btn-sm">&larr; Terug naar overzicht</a></div>

        <section class="bg-base-100 border border-base-300 rounded-lg shadow-sm p-5 md:p-6">
            <div class="flex flex-col md:flex-row gap-4 md:items-center">
                <div class="avatar shrink-0">
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
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,65fr)_minmax(0,35fr)] gap-4 items-start">
            <article class="bg-base-100 border border-base-300 rounded-lg shadow-sm p-5 md:p-6">
                <h2 class="text-2xl font-semibold mb-4">Overlijdensbericht</h2>
                <div class="prose max-w-none">{!! $notice->content !!}</div>
            </article>

            <aside class="space-y-4">
                <section class="bg-base-100 border border-base-300 rounded-lg shadow-sm p-5">
                    <h2 class="text-xl font-semibold mb-3">Informatie over de uitvaart</h2>

                    @if($hasFuneralInfo)
                        <dl class="space-y-3 text-sm">
                            @if($notice->funeral_company_name)
                                <div>
                                    <dt class="font-medium text-base-content/60">Uitvaartondernemer</dt>
                                    <dd>{{ $notice->funeral_company_name }}</dd>
                                </div>
                            @endif
                            @if($notice->funeral_company_contact)
                                <div>
                                    <dt class="font-medium text-base-content/60">Contactpersoon</dt>
                                    <dd>{{ $notice->funeral_company_contact }}</dd>
                                </div>
                            @endif
                            @if($notice->funeral_company_phone)
                                <div>
                                    <dt class="font-medium text-base-content/60">Telefoon</dt>
                                    <dd><a href="tel:{{ $notice->funeral_company_phone }}" class="link link-hover">{{ $notice->funeral_company_phone }}</a></dd>
                                </div>
                            @endif
                            @if($notice->funeral_company_email)
                                <div>
                                    <dt class="font-medium text-base-content/60">E-mail</dt>
                                    <dd><a href="mailto:{{ $notice->funeral_company_email }}" class="link link-hover break-all">{{ $notice->funeral_company_email }}</a></dd>
                                </div>
                            @endif
                            @if($notice->funeral_company_url)
                                <div>
                                    <dt class="font-medium text-base-content/60">Website</dt>
                                    <dd><a href="{{ $notice->funeral_company_url }}" target="_blank" rel="noopener nofollow" class="link link-hover break-all">{{ $notice->funeral_company_url }}</a></dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-sm text-base-content/70">Er is nog geen uitvaartinformatie toegevoegd.</p>
                    @endif
                </section>

                <section class="bg-base-100 border border-base-300 rounded-lg shadow-sm p-5">
                    <h2 class="text-xl font-semibold mb-3">Condoleanceregister</h2>

                    @if($notice->condolence_url)
                        <p class="text-sm text-base-content/70 mb-4">Laat een bericht achter in het externe condoleanceregister.</p>
                        <a href="{{ $notice->condolence_url }}" target="_blank" rel="noopener nofollow" class="btn btn-primary w-full">Naar condoleanceregister</a>
                    @else
                        <p class="text-sm text-base-content/70">Er is geen extern condoleanceregister gekoppeld. U kunt hieronder een bericht achterlaten.</p>
                    @endif
                </section>
            </aside>
        </div>

        <section id="condoleances" class="bg-base-100 border border-base-300 rounded-lg shadow-sm p-5 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-2xl font-semibold">Reacties en condoleances</h2>
                    <p class="text-sm text-base-content/70">Laat een persoonlijk bericht achter voor de nabestaanden.</p>
                </div>
                <div class="badge badge-outline">{{ $notice->condolences->count() }} reactie{{ $notice->condolences->count() === 1 ? '' : 's' }}</div>
            </div>

            @if(session('condolence_success'))
                <div role="alert" class="alert alert-success mb-4">
                    <span>{{ session('condolence_success') }}</span>
                </div>
            @endif

            <form id="condoleance-form" method="POST" action="{{ route('notice.condolences.store', $notice->slug) }}" class="bg-base-200/60 border border-base-300 rounded-lg p-4 mb-6">
                @csrf
                <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="hp_condolence_{{ $notice->id }}">Website</label>
                    <input type="text" id="hp_condolence_{{ $notice->id }}" name="website" value="" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="form-control w-full">
                        <span class="label-text">Voornaam</span>
                        <input name="first_name" value="{{ old('first_name') }}" required class="input input-bordered w-full" />
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text">Achternaam</span>
                        <input name="last_name" value="{{ old('last_name') }}" required class="input input-bordered w-full" />
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text">E-mailadres</span>
                        <input type="email" name="email" value="{{ old('email') }}" required class="input input-bordered w-full" />
                    </label>
                </div>

                <label class="form-control w-full mt-3">
                    <span class="label-text">Bericht</span>
                    <div class="bg-white rounded-box border border-base-300 overflow-hidden">
                        <div id="condolence-editor" style="min-height: 8rem;">{!! old('message') !!}</div>
                    </div>
                    <textarea name="message" id="condolence-input" class="hidden">{{ old('message') }}</textarea>
                </label>

                <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
                <script>
                    (function () {
                        var toolbarOptions = [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['clean'],
                        ];

                        var quill = new Quill('#condolence-editor', {
                            theme: 'snow',
                            modules: { toolbar: toolbarOptions },
                            placeholder: 'Schrijf hier uw condoleance...',
                        });

                        var hiddenInput = document.getElementById('condolence-input');

                        quill.on('text-change', function () {
                            hiddenInput.value = quill.root.innerHTML;
                        });

                        hiddenInput.closest('form').addEventListener('submit', function () {
                            hiddenInput.value = quill.root.innerHTML;
                        });
                    })();
                </script>

                @if($errors->condolence->any())
                    <div role="alert" class="alert alert-error mt-3">
                        <span>{{ $errors->condolence->first() }}</span>
                    </div>
                @endif

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-primary">Condoleance plaatsen</button>
                </div>
            </form>

            <div class="space-y-3">
                @forelse($notice->condolences as $condolence)
                    <article class="border border-base-300 rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                            <h3 class="font-semibold">{{ $condolence->first_name }} {{ $condolence->last_name }}</h3>
                            <time datetime="{{ $condolence->created_at?->toDateString() }}" class="text-sm text-base-content/60">
                                {{ $condolence->created_at?->format('d-m-Y H:i') }}
                            </time>
                        </div>
                        <div class="prose prose-sm max-w-none text-base-content/80">{!! $condolence->message !!}</div>
                    </article>
                @empty
                    <p class="text-sm text-base-content/70">Er zijn nog geen reacties geplaatst.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
