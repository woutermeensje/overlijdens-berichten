@extends('layouts.public')

@section('title', 'Organisatie toevoegen')
@section('meta_description', 'Meld je uitvaartonderneming, crematorium, begraafplaats of andere organisatie uit de uitvaartbranche aan bij overlijdens-berichten.nl.')

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

@section('content')
    <section class="max-w-3xl mx-auto space-y-6">
        <header class="space-y-2">
            <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">Organisatie toevoegen</h1>
            <p class="text-base-content/70">
                Werk je in de uitvaartbranche? Meld je organisatie aan, zodat nabestaanden je makkelijk kunnen vinden.
                Je vermelding verschijnt direct in het <a href="{{ route('organizations.index') }}" class="link">overzicht van organisaties</a>.
            </p>
        </header>

        @if(session('organization_success'))
            <div role="alert" class="alert alert-success">
                <i class="ph ph-check-circle text-xl" aria-hidden="true"></i>
                <span>{{ session('organization_success') }} <a href="{{ route('organizations.index') }}" class="link font-medium">Bekijk het overzicht</a></span>
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

        <form method="POST" action="{{ route('organizations.store') }}" class="space-y-6">
            @csrf
            {!! \App\Support\FormTimer::field() !!}
            <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                <label for="hp_organization">Website</label>
                <input type="text" id="hp_organization" name="website_hp" value="" tabindex="-1" autocomplete="off">
            </div>

            <fieldset class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                <legend class="sr-only">Organisatie</legend>
                <h2 class="text-lg font-semibold">Organisatie</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Bedrijfsnaam <span class="text-error" aria-hidden="true">*</span></span>
                        <input class="input input-bordered w-full" name="name" value="{{ old('name') }}" autocomplete="organization" required maxlength="150">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Categorie <span class="text-error" aria-hidden="true">*</span></span>
                        <select class="select select-bordered w-full" name="category" required>
                            @php($selectedCategory = old('category', request()->query('categorie')))
                            <option value="" disabled @selected(! array_key_exists((string) $selectedCategory, $categories))>Kies een categorie</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Over ons <span class="text-error" aria-hidden="true">*</span></span>
                    <textarea class="textarea textarea-bordered w-full" name="about" rows="6" required minlength="20" maxlength="3000"
                              placeholder="Vertel wie jullie zijn, wat jullie doen en in welke regio jullie werken.">{{ old('about') }}</textarea>
                </label>
            </fieldset>

            <fieldset class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                <legend class="sr-only">Locatie</legend>
                <h2 class="text-lg font-semibold">Locatie</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control w-full relative" data-place-picker>
                        <label for="organization-place" class="label-text font-medium text-sm">Vestigingsplaats <span class="text-error" aria-hidden="true">*</span></label>
                        <input type="text" id="organization-place" name="city" value="{{ $oldPlace['name'] ?? old('city') }}" required
                               placeholder="Begin te typen, bijvoorbeeld Utrecht" class="input input-bordered w-full"
                               autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="organization-place-list" data-place-input>
                        <input type="hidden" name="place_id" value="{{ $oldPlace ? old('place_id') : '' }}" data-place-id>
                        <ul id="organization-place-list" role="listbox" class="hidden absolute top-full z-30 left-0 right-0 mt-1 menu menu-sm bg-base-100 border border-base-300 rounded-box max-h-72 overflow-y-auto flex-nowrap" data-place-list></ul>
                        <p class="hidden text-error text-xs" data-place-error>Kies een plaats uit de lijst met suggesties.</p>
                    </div>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Provincie</span>
                        <input class="input input-bordered w-full bg-base-200/60" value="{{ $oldPlace['province'] ?? '' }}" readonly tabindex="-1"
                               placeholder="Wordt ingevuld na het kiezen van de plaats" data-place-province>
                    </label>
                </div>

                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Adres <span class="font-normal text-base-content/60">(optioneel)</span></span>
                    <input class="input input-bordered w-full" name="address" value="{{ old('address') }}" autocomplete="street-address" placeholder="Straat en huisnummer" maxlength="190">
                </label>
            </fieldset>

            <fieldset class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                <legend class="sr-only">Contactgegevens</legend>
                <h2 class="text-lg font-semibold">Contactgegevens</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">E-mailadres <span class="text-error" aria-hidden="true">*</span></span>
                        <input type="email" class="input input-bordered w-full" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-medium text-sm">Telefoonnummer <span class="font-normal text-base-content/60">(optioneel)</span></span>
                        <input type="tel" class="input input-bordered w-full" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                    </label>
                </div>

                <label class="form-control w-full">
                    <span class="label-text font-medium text-sm">Website <span class="font-normal text-base-content/60">(optioneel)</span></span>
                    <input type="text" inputmode="url" class="input input-bordered w-full" name="website" value="{{ old('website') }}" autocomplete="url" placeholder="www.jouwbedrijf.nl">
                </label>
            </fieldset>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-base-content/60">Velden met <span class="text-error">*</span> zijn verplicht. We gebruiken je gegevens alleen voor je vermelding en om contact met je op te nemen.</p>
                <button type="submit" class="btn btn-primary btn-lg">Organisatie aanmelden</button>
            </div>
        </form>
    </section>

    @include('partials.place-picker-script')
@endsection
