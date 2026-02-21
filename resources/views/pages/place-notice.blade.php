@extends('layouts.public')

@section('title', 'Overlijdensbericht plaatsen')

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm max-w-3xl mx-auto">
        <div class="card-body gap-4">
            <h1 class="card-title text-2xl">Overlijdensbericht plaatsen</h1>
            <p class="text-base-content/70">Plaats hier een overlijdensbericht, familiebericht of rouwadvertentie.</p>

            <div class="card bg-base-200 border border-base-300">
                <div class="card-body">
                    <div class="flex justify-between items-center">
                        <h2 class="card-title text-lg">Gratis pakket</h2>
                        <div class="badge badge-success">€ 0,00</div>
                    </div>
                    <ul class="list-disc list-inside text-base-content/80 space-y-1">
                        <li>Publiceer overlijdensbericht, familiebericht of rouwadvertentie</li>
                        <li>Bericht direct zichtbaar in het overzicht</li>
                        <li>Zelf beheren via je account</li>
                    </ul>
                    <div>
                        <a href="{{ route('register') }}" class="btn btn-primary">Bericht plaatsen</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
