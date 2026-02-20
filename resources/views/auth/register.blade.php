@extends('layouts.public')

@section('title', 'Account aanmaken')

@section('content')
    <section class="card" style="max-width:560px;margin:0 auto;">
        <h1 style="margin-top:0;">Particulier account aanmaken</h1>
        <form method="post" action="{{ route('register.store') }}" class="grid">
            @csrf
            <label>Naam
                <input type="text" name="name" value="{{ old('name') }}" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            </label>
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            </label>
            <label>Wachtwoord
                <input type="password" name="password" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            </label>
            <label>Herhaal wachtwoord
                <input type="password" name="password_confirmation" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            </label>
            @if($errors->any())<div style="color:#9e2b2b;">{{ $errors->first() }}</div>@endif
            <button type="submit" style="padding:11px 14px;background:#1e4c73;color:#fff;border:none;border-radius:10px;cursor:pointer;">Account aanmaken</button>
        </form>
    </section>
@endsection
