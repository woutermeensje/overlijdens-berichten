@extends('layouts.account')

@section('title', 'Bericht bewerken')

@section('content')
    <h1 style="margin-top:0;">Bericht bewerken</h1>
    @include('account.notices._form', ['notice' => $notice])
@endsection
