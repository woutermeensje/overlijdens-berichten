@extends('layouts.public')

@section('title', 'Bericht plaatsen')
@section('meta_description', 'Plaats gratis een overlijdensbericht, familiebericht of rouwadvertentie.')
@section('meta_robots', 'noindex,follow')

@section('content')
    <section class="max-w-4xl mx-auto space-y-6">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">Bericht plaatsen</h1>
                <p class="text-sm sm:text-base text-base-content/70">Plaats gratis een overlijdensbericht, familiebericht of rouwadvertentie.</p>
            </div>
            <span class="badge badge-outline">Gratis pakket</span>
        </header>

        <div class="space-y-6">
            @if($errors->any())
                <div class="alert alert-error">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('notice.wizard.submit') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="hp_website">Website</label>
                    <input type="text" id="hp_website" name="website" value="" tabindex="-1" autocomplete="off">
                </div>

                <fieldset class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                    <legend class="sr-only">Type bericht</legend>
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Type bericht</h2>
                        <p class="text-sm text-base-content/70">Kies wat voor bericht u wilt plaatsen. De standaardberichten zijn gratis.</p>
                    </div>
                    @php
                        $featuredPrice = \App\Models\MemorialNotice::FEATURED_PRICE_EXCL_VAT;
                        $typeOptions = collect(\App\Models\MemorialNotice::typeLabels())
                            ->map(fn (string $label): array => ['label' => $label, 'description' => null, 'price' => '€ 0,00', 'note' => 'Gratis', 'noteClass' => 'text-success'])
                            ->put(\App\Models\MemorialNotice::FEATURED_OPTION, [
                                'label' => 'Uitgelicht overlijdensbericht',
                                'description' => 'Staat bovenaan de lijst met overlijdensberichten. U ontvangt achteraf een factuur.',
                                'price' => '€ '.number_format($featuredPrice, 2, ',', '.'),
                                'note' => 'excl. btw',
                                'noteClass' => 'text-base-content/60',
                            ]);
                        $selectedType = old('type', \App\Models\MemorialNotice::TYPE_OVERLIJDENSBERICHT);
                        if (! $typeOptions->has($selectedType)) {
                            $selectedType = \App\Models\MemorialNotice::TYPE_OVERLIJDENSBERICHT;
                        }
                    @endphp
                    <div id="type-select" class="relative">
                        <input type="hidden" name="type" id="type-input" value="{{ $selectedType }}">
                        <button type="button" id="type-select-button" class="w-full flex items-center justify-between gap-3 rounded-box border border-base-300 bg-base-100 px-4 py-3 text-left cursor-pointer hover:bg-base-200/50 focus:outline-none focus-visible:border-primary aria-expanded:border-primary" aria-haspopup="listbox" aria-expanded="false" aria-controls="type-select-list">
                            <span id="type-select-current" class="flex-1 min-w-0">
                                @foreach ($typeOptions as $value => $option)
                                    <span data-value="{{ $value }}" class="flex items-center justify-between gap-3" @if($value !== $selectedType) hidden @endif>
                                        <span class="min-w-0">
                                            <span class="block font-medium">{{ $option['label'] }}</span>
                                            @if($option['description'])
                                                <span class="block text-xs text-base-content/70">{{ $option['description'] }}</span>
                                            @endif
                                        </span>
                                        <span class="text-sm text-right whitespace-nowrap">
                                            <span class="font-semibold">{{ $option['price'] }}</span>
                                            <span class="block text-xs {{ $option['noteClass'] }}">{{ $option['note'] }}</span>
                                        </span>
                                    </span>
                                @endforeach
                            </span>
                            <i class="ph ph-caret-down shrink-0 text-base-content/60" aria-hidden="true"></i>
                        </button>
                        <ul id="type-select-list" role="listbox" aria-label="Type bericht" tabindex="-1" class="absolute z-20 mt-1 w-full rounded-box border border-base-300 bg-base-100 p-1 max-h-96 overflow-auto focus:outline-none" hidden>
                            @foreach ($typeOptions as $value => $option)
                                <li role="option" data-value="{{ $value }}" aria-selected="{{ $value === $selectedType ? 'true' : 'false' }}" class="flex items-center justify-between gap-3 rounded-box px-3 py-2 cursor-pointer hover:bg-base-200 data-[active]:bg-base-200 aria-selected:font-semibold">
                                    <span class="min-w-0">
                                        <span class="block font-medium">{{ $option['label'] }}</span>
                                        @if($option['description'])
                                            <span class="block text-xs font-normal text-base-content/70">{{ $option['description'] }}</span>
                                        @endif
                                    </span>
                                    <span class="text-sm text-right whitespace-nowrap">
                                        <span class="font-semibold">{{ $option['price'] }}</span>
                                        <span class="block text-xs font-normal {{ $option['noteClass'] }}">{{ $option['note'] }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </fieldset>

                <fieldset id="billing-details" class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4" @if(old('type') !== \App\Models\MemorialNotice::FEATURED_OPTION) hidden disabled @endif>
                    <legend class="sr-only">Factuurgegevens</legend>
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Factuurgegevens</h2>
                        <p class="text-sm text-base-content/70">Het bericht wordt direct geplaatst. De factuur van € {{ number_format($featuredPrice, 2, ',', '.') }} excl. btw sturen we naar dit adres.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Naam</span>
                            <input class="input input-bordered w-full" name="billing_name" value="{{ old('billing_name') }}" autocomplete="name" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Bedrijfsnaam (optioneel)</span>
                            <input class="input input-bordered w-full" name="billing_company" value="{{ old('billing_company') }}" autocomplete="organization">
                        </label>
                    </div>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">E-mailadres</span>
                        <input type="email" class="input input-bordered w-full" name="billing_email" value="{{ old('billing_email') }}" autocomplete="email" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Straat en huisnummer</span>
                        <input class="input input-bordered w-full" name="billing_address" value="{{ old('billing_address') }}" autocomplete="street-address" required>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Postcode</span>
                            <input class="input input-bordered w-full" name="billing_postcode" value="{{ old('billing_postcode') }}" autocomplete="postal-code" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Plaats</span>
                            <input class="input input-bordered w-full" name="billing_city" value="{{ old('billing_city') }}" autocomplete="address-level2" required>
                        </label>
                    </div>
                </fieldset>

                <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Gegevens van de overledene</h2>
                        <p class="text-sm text-base-content/70">Vul hier de gegevens in van degene die is overleden, niet uw eigen gegevens.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Voornaam</span>
                            <input class="input input-bordered w-full" name="deceased_first_name" value="{{ old('deceased_first_name') }}" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Achternaam</span>
                            <input class="input input-bordered w-full" name="deceased_last_name" value="{{ old('deceased_last_name') }}">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Geboortedatum</span>
                            <input type="date" class="input input-bordered w-full" name="born_date" value="{{ old('born_date') }}">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Overlijdensdatum</span>
                            <input type="date" class="input input-bordered w-full" name="died_date" value="{{ old('died_date') }}" required>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Stad</span>
                            <input class="input input-bordered w-full" name="city" value="{{ old('city') }}">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Provincie</span>
                            <input class="input input-bordered w-full" name="province" value="{{ old('province') }}">
                        </label>
                    </div>
                </div>

                <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Foto van de overledene</h2>
                        <p class="text-sm text-base-content/70">Optioneel. Zonder foto tonen we een kaarsje.</p>
                    </div>
                    <label id="photo-dropzone" for="photo-input" class="flex flex-col sm:flex-row items-center gap-5 rounded-box border border-base-300 bg-base-200/40 p-5 cursor-pointer transition-colors hover:bg-base-200 data-[dragging]:bg-base-200 data-[dragging]:border-primary">
                        <span class="avatar shrink-0">
                            <span class="w-24 rounded-full border border-base-300 bg-base-100">
                                <img id="photo-preview" src="{{ asset('images/candle-placeholder.svg') }}" alt="" data-placeholder="{{ asset('images/candle-placeholder.svg') }}">
                            </span>
                        </span>
                        <span class="flex-1 text-center sm:text-left space-y-1">
                            <span class="flex items-center justify-center sm:justify-start gap-2 font-medium">
                                <i class="ph ph-upload-simple text-lg" aria-hidden="true"></i>
                                <span id="photo-label">Kies een foto of sleep hem hierheen</span>
                            </span>
                            <span class="block text-sm text-base-content/60">JPG, PNG of WEBP, maximaal 5 MB</span>
                        </span>
                        <button type="button" id="photo-remove" class="btn btn-sm btn-ghost" hidden>Verwijderen</button>
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                </div>

                <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                    <h2 class="text-lg font-semibold">Inhoud</h2>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Titel (optioneel)</span>
                        <input class="input input-bordered w-full" name="title" value="{{ old('title') }}" placeholder="Laat leeg voor automatische naam">
                    </label>

                    <div class="form-control w-full">
                        <label for="content-input" class="label-text font-medium text-sm">Berichttekst</label>
                        <textarea name="content" id="content-input" data-rich-text data-editor-height="260" placeholder="Schrijf hier de tekst van het overlijdensbericht..." class="textarea textarea-bordered w-full" rows="9">{{ old('content') }}</textarea>
                        @include('partials.rich-text-editor')
                    </div>
                </div>

                <fieldset class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                    <legend class="sr-only">Uw contactgegevens</legend>
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Uw contactgegevens</h2>
                        <p class="text-sm text-base-content/70">Zodat we contact met u kunnen opnemen over dit bericht. Deze gegevens worden niet getoond op de website.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Uw naam</span>
                            <input class="input input-bordered w-full" name="placer_name" value="{{ old('placer_name', auth()->user()?->name) }}" autocomplete="name" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Telefoonnummer (optioneel)</span>
                            <input type="tel" class="input input-bordered w-full" name="placer_phone" value="{{ old('placer_phone') }}" autocomplete="tel">
                        </label>
                    </div>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Uw e-mailadres</span>
                        <input type="email" class="input input-bordered w-full" name="placer_email" value="{{ old('placer_email', auth()->user()?->email) }}" autocomplete="email" required>
                    </label>

                    @auth
                        <p class="text-sm text-base-content/70">
                            <i class="ph ph-user-circle" aria-hidden="true"></i>
                            Het bericht wordt gekoppeld aan uw account, zodat u het later kunt bewerken.
                        </p>
                    @else
                        <div class="rounded-box border border-base-300 bg-base-200/40 p-4 space-y-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="hidden" name="create_account" value="0">
                                <input type="checkbox" id="create-account" name="create_account" value="1" class="checkbox checkbox-primary checkbox-sm mt-0.5" @checked(old('create_account'))>
                                <span>
                                    <span class="block font-medium">Ik wil een account aanmaken</span>
                                    <span class="block text-sm text-base-content/70">Dan kunt u het bericht na plaatsing zelf nog bewerken.</span>
                                </span>
                            </label>

                            <div id="account-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4" @unless(old('create_account')) hidden @endunless>
                                <label class="form-control w-full">
                                    <span class="label-text font-medium text-sm">Kies een wachtwoord</span>
                                    <input type="password" class="input input-bordered w-full" name="password" autocomplete="new-password" minlength="8" @unless(old('create_account')) disabled @endunless required>
                                    <span class="text-xs text-base-content/60">Minimaal 8 tekens</span>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text font-medium text-sm">Herhaal wachtwoord</span>
                                    <input type="password" class="input input-bordered w-full" name="password_confirmation" autocomplete="new-password" minlength="8" @unless(old('create_account')) disabled @endunless required>
                                </label>
                            </div>

                            <p class="text-sm text-base-content/70">Heeft u al een account? <a href="{{ route('login') }}" class="link">Log eerst in</a>.</p>
                        </div>
                    @endauth
                </fieldset>

                <div class="flex justify-end pt-3 border-t border-base-300">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto sm:min-w-44">Bericht publiceren</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Foto: voorbeeld tonen, slepen ondersteunen en weer kunnen verwijderen.
        (function () {
            var dropzone = document.getElementById('photo-dropzone');
            var input = document.getElementById('photo-input');
            var preview = document.getElementById('photo-preview');
            var label = document.getElementById('photo-label');
            var remove = document.getElementById('photo-remove');
            var defaultLabel = label.textContent;

            function render() {
                var file = input.files[0];
                if (preview.src.indexOf('blob:') === 0) URL.revokeObjectURL(preview.src);
                preview.src = file ? URL.createObjectURL(file) : preview.dataset.placeholder;
                label.textContent = file ? file.name : defaultLabel;
                remove.hidden = !file;
            }

            input.addEventListener('change', render);

            remove.addEventListener('click', function (event) {
                event.preventDefault();
                input.value = '';
                render();
            });

            ['dragenter', 'dragover'].forEach(function (type) {
                dropzone.addEventListener(type, function (event) {
                    event.preventDefault();
                    dropzone.setAttribute('data-dragging', '');
                });
            });

            ['dragleave', 'drop'].forEach(function (type) {
                dropzone.addEventListener(type, function () {
                    dropzone.removeAttribute('data-dragging');
                });
            });

            dropzone.addEventListener('drop', function (event) {
                event.preventDefault();
                if (!event.dataTransfer.files.length) return;
                var transfer = new DataTransfer();
                transfer.items.add(event.dataTransfer.files[0]);
                input.files = transfer.files;
                render();
            });
        })();

        // Account aanmaken: wachtwoordvelden alleen tonen (en versturen) als het vinkje aan staat.
        (function () {
            var checkbox = document.getElementById('create-account');
            if (!checkbox) return;
            var fields = document.getElementById('account-fields');

            checkbox.addEventListener('change', function () {
                fields.hidden = !checkbox.checked;
                fields.querySelectorAll('input').forEach(function (input) {
                    input.disabled = !checkbox.checked;
                });
            });
        })();

        // Type bericht: single select met prijzen. Factuurgegevens alleen tonen (en verplicht maken) bij de uitgelichte optie.
        (function () {
            var featuredValue = '{{ \App\Models\MemorialNotice::FEATURED_OPTION }}';
            var input = document.getElementById('type-input');
            var button = document.getElementById('type-select-button');
            var list = document.getElementById('type-select-list');
            var options = Array.prototype.slice.call(list.querySelectorAll('[role="option"]'));
            var current = document.querySelectorAll('#type-select-current [data-value]');
            var billing = document.getElementById('billing-details');
            var activeIndex = -1;

            function setActive(index) {
                activeIndex = (index + options.length) % options.length;
                options.forEach(function (option, i) {
                    option.toggleAttribute('data-active', i === activeIndex);
                });
                options[activeIndex].scrollIntoView({ block: 'nearest' });
            }

            function open() {
                list.hidden = false;
                button.setAttribute('aria-expanded', 'true');
                setActive(options.findIndex(function (option) { return option.dataset.value === input.value; }));
                list.focus();
            }

            function close(focusButton) {
                list.hidden = true;
                button.setAttribute('aria-expanded', 'false');
                if (focusButton) button.focus();
            }

            function select(value) {
                input.value = value;
                options.forEach(function (option) {
                    option.setAttribute('aria-selected', option.dataset.value === value ? 'true' : 'false');
                });
                current.forEach(function (el) {
                    el.hidden = el.dataset.value !== value;
                });

                var featured = value === featuredValue;
                billing.hidden = !featured;
                billing.disabled = !featured;
            }

            button.addEventListener('click', function () {
                list.hidden ? open() : close(true);
            });

            button.addEventListener('keydown', function (event) {
                if (['ArrowDown', 'ArrowUp', 'Enter', ' '].indexOf(event.key) !== -1) {
                    event.preventDefault();
                    open();
                }
            });

            list.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown') { event.preventDefault(); setActive(activeIndex + 1); }
                else if (event.key === 'ArrowUp') { event.preventDefault(); setActive(activeIndex - 1); }
                else if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); select(options[activeIndex].dataset.value); close(true); }
                else if (event.key === 'Escape' || event.key === 'Tab') { close(event.key === 'Escape'); }
            });

            options.forEach(function (option, i) {
                option.addEventListener('mousemove', function () { if (activeIndex !== i) setActive(i); });
                option.addEventListener('click', function () { select(option.dataset.value); close(true); });
            });

            document.addEventListener('click', function (event) {
                if (!list.hidden && !document.getElementById('type-select').contains(event.target)) close(false);
            });
        })();
    </script>
@endsection
