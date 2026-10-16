@extends('layouts.public')

@section('title', 'Opties en pakketten')
@section('meta_description', 'Bekijk de pakketten voor het plaatsen van een overlijdensbericht: gratis, uitgelicht of als uitvaartondernemer.')

@php
    $featuredPrice = \App\Models\MemorialNotice::FEATURED_PRICE_EXCL_VAT;
    $money = fn (float $amount): string => '€ '.number_format($amount, 2, ',', '.');
    $contactEmail = config('app.admin_email');

    $packages = [
        [
            'name' => 'Gratis',
            'intro' => 'Voor iedereen die een overlijdensbericht, familiebericht of rouwadvertentie wil plaatsen.',
            'price' => $money(0),
            'priceNote' => 'Geen kosten',
            'features' => [
                'Overlijdensbericht, familiebericht of rouwadvertentie',
                'Berichttekst met opmaak',
                'Foto van de overledene (of een kaarsje)',
                'Condoleances van bezoekers bij het bericht',
                'Link naar een online condoleanceregister',
                'Optioneel een account om het bericht later te bewerken',
            ],
            'cta' => 'Gratis plaatsen',
            'href' => route('notice.wizard'),
            'highlight' => false,
        ],
        [
            'name' => 'Uitgelicht',
            'intro' => 'Voor wie wil dat het bericht extra opvalt en door meer mensen gezien wordt.',
            'price' => $money($featuredPrice),
            'priceNote' => 'excl. 21% btw ('.$money($featuredPrice * 1.21).' incl. btw)',
            'features' => [
                'Alles uit het gratis pakket',
                'Bovenaan de lijst met overlijdensberichten',
                'Opvallende weergave van het bericht',
                'Extra zichtbaar op de pagina van de woonplaats',
                'Factuur achteraf, geen betaling vooraf',
            ],
            'cta' => 'Uitgelicht plaatsen',
            'href' => route('notice.wizard', ['type' => \App\Models\MemorialNotice::FEATURED_OPTION]),
            'highlight' => true,
        ],
        [
            'name' => 'Uitvaartondernemer',
            'intro' => 'Voor uitvaartondernemers die regelmatig berichten plaatsen namens families.',
            'price' => 'In overleg',
            'priceNote' => 'Prijs afgestemd op uw volume',
            'features' => [
                'Onbeperkt berichten plaatsen en beheren',
                'Eigen account met overzicht van al uw berichten',
                'Bedrijfsprofiel met logo en contactgegevens',
                'Vermelding van uw onderneming bij elk bericht',
                'Voordelig tarief voor uitgelichte berichten',
                'Eén maandfactuur en een vast aanspreekpunt',
            ],
            'cta' => 'Neem contact op',
            'href' => 'mailto:'.$contactEmail.'?subject='.rawurlencode('Pakket uitvaartondernemer'),
            'highlight' => false,
        ],
    ];
@endphp

@section('content')
    <section class="max-w-[1080px] mx-auto space-y-6">
        <header class="space-y-2 text-center max-w-2xl mx-auto">
            <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">Opties en pakketten</h1>
            <p class="text-base-content/70">Kies het pakket dat bij u past. Een overlijdensbericht plaatsen is altijd gratis mogelijk.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:items-stretch">
            @foreach($packages as $package)
                <article class="card bg-base-100 border {{ $package['highlight'] ? 'border-primary border-2' : 'border-base-300' }}">
                    <div class="card-body gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="card-title text-xl">{{ $package['name'] }}</h2>
                                @if($package['highlight'])
                                    <span class="badge badge-primary">Meest zichtbaar</span>
                                @endif
                            </div>
                            <p class="text-sm text-base-content/70">{{ $package['intro'] }}</p>
                        </div>

                        <div>
                            <div class="text-3xl font-semibold">{{ $package['price'] }}</div>
                            <div class="text-sm text-base-content/60">{{ $package['priceNote'] }}</div>
                        </div>

                        <ul class="space-y-2 text-sm flex-1">
                            @foreach($package['features'] as $feature)
                                <li class="flex items-start gap-2">
                                    <i class="ph ph-check text-success text-base mt-0.5 shrink-0" aria-hidden="true"></i>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ $package['href'] }}" class="btn {{ $package['highlight'] ? 'btn-primary' : 'btn-outline' }} w-full">{{ $package['cta'] }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
