@extends('layouts.public')

@section('title', 'Account aanmaken (ondernemer)')

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm max-w-xl mx-auto">
        <div class="card-body">
            <h1 class="card-title text-2xl">Account aanmaken voor uitvaartondernemers</h1>

            <form method="post" action="{{ route('register.store') }}" class="space-y-4">
                @csrf
                <label class="form-control w-full">
                    <span class="label-text">Naam</span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input input-bordered w-full" />
                </label>

                <label class="form-control w-full">
                    <span class="label-text">E-mail</span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="input input-bordered w-full" />
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Wachtwoord</span>
                    <input type="password" name="password" required class="input input-bordered w-full" />
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Herhaal wachtwoord</span>
                    <input type="password" name="password_confirmation" required class="input input-bordered w-full" />
                </label>

                @if($errors->any())
                    <div class="alert alert-error"><span>{{ $errors->first() }}</span></div>
                @endif

                <button type="submit" class="btn btn-primary w-full">Account aanmaken</button>
            </form>
        </div>
    </section>
@endsection
