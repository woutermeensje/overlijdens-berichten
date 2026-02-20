@extends('layouts.account')

@section('title', 'Dashboard')

@section('content')
    <h1 style="margin-top:0;">Dashboard</h1>
    <p style="color:#58656f;">Beheer je overlijdensberichten, familieberichten en rouwadvertenties.</p>

    <div class="grid" style="grid-template-columns: repeat(3, minmax(0, 1fr)); margin:16px 0;">
        <div style="border:1px solid #dde1e6;border-radius:12px;padding:12px;"><strong>{{ $notices->count() }}</strong><br>Totaal berichten</div>
        <div style="border:1px solid #dde1e6;border-radius:12px;padding:12px;"><strong>{{ $notices->where('type', 'overlijdensbericht')->count() }}</strong><br>Overlijdensberichten</div>
        <div style="border:1px solid #dde1e6;border-radius:12px;padding:12px;"><strong>{{ $notices->where('type', 'rouwadvertentie')->count() }}</strong><br>Rouwadvertenties</div>
    </div>
@endsection
