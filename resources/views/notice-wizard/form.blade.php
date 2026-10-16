@extends('layouts.public')

@section('title', 'Bericht plaatsen')
@section('meta_description', 'Plaats gratis een overlijdensbericht, familiebericht of rouwadvertentie.')
@section('meta_robots', 'noindex,follow')

@section('content')
    <section class="max-w-6xl mx-auto space-y-6 pb-24 lg:pb-0">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">Bericht plaatsen</h1>
                <p class="text-sm sm:text-base text-base-content/70">Plaats gratis een overlijdensbericht, familiebericht of rouwadvertentie.</p>
            </div>
            <span class="badge badge-outline">Gratis pakket</span>
        </header>

        <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_17rem] lg:gap-8 lg:items-start">
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

            <form id="notice-form" method="post" action="{{ route('notice.wizard.submit') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="hp_website">Website</label>
                    <input type="text" id="hp_website" name="website" value="" tabindex="-1" autocomplete="off">
                </div>

                <fieldset id="sectie-type" data-progress-section="Type bericht" class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4 scroll-mt-6">
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
                        <input type="hidden" name="type" data-progress-field id="type-input" value="{{ $selectedType }}">
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

                <fieldset id="billing-details" data-progress-section="Factuurgegevens" class="scroll-mt-6 rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4" @if(old('type') !== \App\Models\MemorialNotice::FEATURED_OPTION) hidden disabled @endif>
                    <legend class="sr-only">Factuurgegevens</legend>
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Factuurgegevens</h2>
                        <p class="text-sm text-base-content/70">Het bericht wordt direct geplaatst. De factuur van € {{ number_format($featuredPrice, 2, ',', '.') }} excl. btw sturen we naar dit adres.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Naam</span>
                            <input class="input input-bordered w-full" name="billing_name" data-progress-field value="{{ old('billing_name') }}" autocomplete="name" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Bedrijfsnaam (optioneel)</span>
                            <input class="input input-bordered w-full" name="billing_company" value="{{ old('billing_company') }}" autocomplete="organization">
                        </label>
                    </div>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">E-mailadres</span>
                        <input type="email" class="input input-bordered w-full" name="billing_email" data-progress-field value="{{ old('billing_email') }}" autocomplete="email" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Straat en huisnummer</span>
                        <input class="input input-bordered w-full" name="billing_address" data-progress-field value="{{ old('billing_address') }}" autocomplete="street-address" required>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Postcode</span>
                            <input class="input input-bordered w-full" name="billing_postcode" data-progress-field value="{{ old('billing_postcode') }}" autocomplete="postal-code" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Plaats</span>
                            <input class="input input-bordered w-full" name="billing_city" data-progress-field value="{{ old('billing_city') }}" autocomplete="address-level2" required>
                        </label>
                    </div>
                </fieldset>

                <div id="sectie-overledene" data-progress-section="Gegevens van de overledene" class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4 scroll-mt-6">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Gegevens van de overledene</h2>
                        <p class="text-sm text-base-content/70">Vul hier de gegevens in van degene die is overleden, niet uw eigen gegevens.</p>
                    </div>
                    {{-- Naam van de overledene, niet van de invuller: wachtwoordmanagers/autofill uitschakelen. --}}
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Volledige naam</span>
                        <input class="input input-bordered w-full" name="deceased_name" data-progress-field value="{{ old('deceased_name') }}"
                               placeholder="Bijvoorbeeld Johanna Maria van der Berg" autocomplete="off" data-1p-ignore data-lpignore="true" required>
                    </label>

                    <div class="form-control w-full">
                        <label for="content-input" class="label-text font-medium text-sm">Berichttekst</label>
                        <textarea name="content" data-progress-field id="content-input" data-rich-text data-editor-height="260" placeholder="Schrijf hier de tekst van het overlijdensbericht..." class="textarea textarea-bordered w-full" rows="9">{{ old('content') }}</textarea>
                        @include('partials.rich-text-editor')
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Geboortedatum</span>
                            <input type="date" class="input input-bordered w-full" name="born_date" data-progress-field value="{{ old('born_date') }}">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Overlijdensdatum</span>
                            <input type="date" class="input input-bordered w-full" name="died_date" data-progress-field value="{{ old('died_date') }}" required>
                        </label>
                    </div>

                    @php
                        $oldPlace = null;
                        if (old('place_id')) {
                            try {
                                $oldPlace = app(\App\Services\Geocoder::class)->placeById((string) old('place_id'));
                            } catch (\Throwable) {
                                $oldPlace = null;
                            }
                        }
                    @endphp
                    <div class="form-control w-full relative" data-place-picker>
                        <label for="place-input" class="label-text font-medium text-sm">Woonplaats</label>
                        <input type="text" id="place-input" name="city" data-progress-field value="{{ $oldPlace['name'] ?? old('city') }}"
                               placeholder="Begin te typen, bijvoorbeeld Den Haag" class="input input-bordered w-full"
                               autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="place-list" data-place-input>
                        <input type="hidden" name="place_id" value="{{ $oldPlace ? old('place_id') : '' }}" data-place-id>
                        <ul id="place-list" role="listbox" class="hidden absolute top-full z-30 left-0 right-0 mt-1 menu menu-sm bg-base-100 border border-base-300 rounded-box max-h-72 overflow-y-auto flex-nowrap" data-place-list></ul>
                        <p class="text-xs text-base-content/60 {{ $oldPlace ? '' : 'hidden' }}" data-place-details>{{ $oldPlace ? ($oldPlace['province'] ?? '').' · Nederland' : '' }}</p>
                        <p class="hidden text-error text-xs" data-place-error>Kies een plaats uit de lijst met suggesties.</p>
                    </div>
                </div>

                <div id="sectie-foto" data-progress-section="Foto" data-progress-optional class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4 scroll-mt-6">
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
                        <input type="file" id="photo-input" name="photo" data-progress-field accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                </div>

                <fieldset id="sectie-contact" data-progress-section="Uw contactgegevens" class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4 scroll-mt-6">
                    <legend class="sr-only">Uw contactgegevens</legend>
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">Uw contactgegevens</h2>
                        <p class="text-sm text-base-content/70">Zodat we contact met u kunnen opnemen over dit bericht. Deze gegevens worden niet getoond op de website.</p>
                    </div>
                    @php
                        // Bij een ingelogde gebruiker de accountnaam opsplitsen: eerste woord als voornaam, de rest als achternaam.
                        $accountName = explode(' ', trim((string) auth()->user()?->name), 2);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Uw voornaam</span>
                            <input class="input input-bordered w-full" name="placer_first_name" data-progress-field value="{{ old('placer_first_name', $accountName[0]) }}" autocomplete="given-name" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Uw achternaam</span>
                            <input class="input input-bordered w-full" name="placer_last_name" data-progress-field value="{{ old('placer_last_name', $accountName[1] ?? '') }}" autocomplete="family-name" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Uw e-mailadres</span>
                            <input type="email" class="input input-bordered w-full" name="placer_email" data-progress-field value="{{ old('placer_email', auth()->user()?->email) }}" autocomplete="email" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Telefoonnummer (optioneel)</span>
                            <input type="tel" class="input input-bordered w-full" name="placer_phone" value="{{ old('placer_phone') }}" autocomplete="tel">
                        </label>
                    </div>

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
                                    <input type="password" class="input input-bordered w-full" name="password" data-progress-field autocomplete="new-password" minlength="8" @unless(old('create_account')) disabled @endunless required>
                                    <span class="text-xs text-base-content/60">Minimaal 8 tekens</span>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text font-medium text-sm">Herhaal wachtwoord</span>
                                    <input type="password" class="input input-bordered w-full" name="password_confirmation" data-progress-field autocomplete="new-password" minlength="8" @unless(old('create_account')) disabled @endunless required>
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

        {{-- Voortgang: meebewegende zijbalk op desktop --}}
        <aside class="hidden lg:block sticky top-6" aria-label="Voortgang">
            <div class="rounded-box border border-base-300 bg-base-100 p-5 space-y-4">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="font-semibold">Voortgang</h2>
                    <span class="text-2xl font-semibold" data-progress-percent>0%</span>
                </div>
                <progress class="progress text-[#5a3a3a] w-full" value="0" max="100" data-progress-bar></progress>
                <ol class="space-y-2 text-sm" id="progress-steps"></ol>
            </div>
        </aside>
        </div>

        {{-- Voortgang: strook onderin op mobiel --}}
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-base-100 border-t border-base-300 px-4 py-3" aria-hidden="true">
            <div class="max-w-6xl mx-auto flex items-center gap-3">
                <progress class="progress text-[#5a3a3a] flex-1" value="0" max="100" data-progress-bar></progress>
                <span class="text-sm font-semibold whitespace-nowrap"><span data-progress-percent>0%</span> ingevuld</span>
            </div>
        </div>
    </section>

    @include('partials.place-picker-script')

    <script>
        // Voortgang: percentage van ingevulde velden (uitgeschakelde secties tellen niet mee) en een checklist per blok.
        (function () {
            var form = document.getElementById('notice-form');
            var sections = Array.prototype.slice.call(form.querySelectorAll('[data-progress-section]'));
            var list = document.getElementById('progress-steps');
            var items = sections.map(function (section) {
                var li = document.createElement('li');
                li.innerHTML = '<a class="flex items-center gap-2 hover:underline" href="#' + section.id + '">'
                    + '<i class="ph ph-circle text-base-content/40" aria-hidden="true"></i>'
                    + '<span></span></a>';
                li.querySelector('span').textContent = section.dataset.progressSection
                    + (section.hasAttribute('data-progress-optional') ? ' (optioneel)' : '');
                list.appendChild(li);
                return li;
            });

            function filled(field) {
                if (field.type === 'file') return field.files.length > 0;
                var value = field.value || '';
                if (field.tagName === 'TEXTAREA') value = value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ');
                return value.trim() !== '';
            }

            function activeFields(section) {
                return Array.prototype.slice.call(section.querySelectorAll('[data-progress-field]')).filter(function (field) {
                    return !field.disabled && !field.closest('[hidden], fieldset[disabled]');
                });
            }

            function update() {
                var total = 0;
                var done = 0;

                sections.forEach(function (section, i) {
                    var inactive = section.hidden || section.disabled;
                    var fields = activeFields(section);
                    var count = fields.filter(filled).length;
                    var complete = fields.length > 0 && count === fields.length;

                    if (!inactive && !section.hasAttribute('data-progress-optional')) {
                        total += fields.length;
                        done += count;
                    }

                    items[i].hidden = inactive;
                    var icon = items[i].querySelector('i');
                    icon.className = complete
                        ? 'ph ph-check-circle text-success'
                        : 'ph ph-circle text-base-content/40';
                });

                var percent = total ? Math.round(done / total * 100) : 0;
                document.querySelectorAll('[data-progress-percent]').forEach(function (el) { el.textContent = percent + '%'; });
                document.querySelectorAll('[data-progress-bar]').forEach(function (el) { el.value = percent; });
            }

            var pending;
            function schedule() {
                clearTimeout(pending);
                pending = setTimeout(update, 50);
            }

            ['input', 'change', 'click', 'keyup', 'drop'].forEach(function (type) {
                form.addEventListener(type, schedule);
            });
            document.addEventListener('click', schedule);
            window.addEventListener('load', update);
            update();
        })();

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

        // Factuurnaam/-e-mail worden vooraf ingevuld met de contactgegevens, tot de gebruiker ze zelf aanpast.
        (function () {
            var field = function (name) { return document.querySelector('[name="' + name + '"]'); };
            var billingName = field('billing_name');
            var billingEmail = field('billing_email');
            var firstName = field('placer_first_name');
            var lastName = field('placer_last_name');
            var email = field('placer_email');
            if (!billingName || !billingEmail || !firstName || !lastName || !email) return;

            [billingName, billingEmail].forEach(function (target) {
                target.dataset.touched = target.value.trim() !== '' ? '1' : '';
                target.addEventListener('input', function () { target.dataset.touched = '1'; });
            });

            var sync = function () {
                if (!billingName.dataset.touched) billingName.value = (firstName.value.trim() + ' ' + lastName.value.trim()).trim();
                if (!billingEmail.dataset.touched) billingEmail.value = email.value;
            };

            [firstName, lastName, email].forEach(function (source) { source.addEventListener('input', sync); });
            sync();
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
