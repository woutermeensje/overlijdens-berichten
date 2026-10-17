@extends('layouts.public')

@section('title', 'Pagina niet gevonden')
@section('meta_description', 'Deze pagina bestaat niet (meer). Zoek een overlijdensbericht op naam of plaats via de homepage.')
@section('meta_robots', 'noindex,follow')

@section('content')
    <section class="card bg-base-100 border border-base-300 max-w-2xl mx-auto">
        <div class="card-body gap-4 text-center items-center">
            <h1 class="text-2xl font-semibold">Pagina niet gevonden</h1>
            <p class="text-base-content/70">
                De pagina die je zoekt bestaat niet (meer). Misschien is het bericht offline gehaald of is de link veranderd.
            </p>
            <div class="flex flex-wrap justify-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-primary">Naar de overlijdensberichten</a>
                <a href="{{ route('contact') }}" class="btn btn-outline">Contact</a>
            </div>
        </div>
    </section>
@endsection
