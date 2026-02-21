@extends('layouts.account')

@section('title', 'Nieuw bericht')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Nieuw bericht publiceren</h1>
    @include('account.notices._form')
@endsection
