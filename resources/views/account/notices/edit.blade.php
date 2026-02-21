@extends('layouts.account')

@section('title', 'Bericht bewerken')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Bericht bewerken</h1>
    @include('account.notices._form', ['notice' => $notice])
@endsection
