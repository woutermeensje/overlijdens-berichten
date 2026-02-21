@extends('layouts.public')

@section('title', 'Inloggen')

@section('content')
    <section class="card bg-base-100 border border-base-300 shadow-sm max-w-xl mx-auto">
        <div class="card-body">
            <h1 class="card-title text-2xl">Inloggen</h1>

            <form method="post" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <label class="form-control w-full">
                    <span class="label-text">E-mail</span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="input input-bordered w-full" />
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Wachtwoord</span>
                    <input type="password" name="password" required class="input input-bordered w-full" />
                </label>

                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" name="remember" value="1" class="checkbox checkbox-sm" />
                    <span class="label-text">Onthoud mij</span>
                </label>

                @if($errors->any())
                    <div class="alert alert-error"><span>{{ $errors->first() }}</span></div>
                @endif

                <button type="submit" class="btn btn-primary w-full">Inloggen</button>
            </form>
        </div>
    </section>
@endsection
