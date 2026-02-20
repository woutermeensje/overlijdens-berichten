@extends('layouts.public')

@section('title', 'Inloggen')

@section('content')
    <section class="card" style="max-width:560px;margin:0 auto;">
        <h1 style="margin-top:0;">Inloggen</h1>
        <form method="post" action="{{ route('login.attempt') }}" class="grid">
            @csrf
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            </label>
            <label>Wachtwoord
                <input type="password" name="password" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            </label>
            <label><input type="checkbox" name="remember" value="1"> Onthoud mij</label>
            @if($errors->any())<div style="color:#9e2b2b;">{{ $errors->first() }}</div>@endif
            <button type="submit" style="padding:11px 14px;background:#1e4c73;color:#fff;border:none;border-radius:10px;cursor:pointer;">Inloggen</button>
        </form>
    </section>
@endsection
