@extends('layouts.account')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-semibold mb-2">Dashboard</h1>
    <p class="text-base-content/70 mb-4">Beheer je overlijdensberichten, familieberichten en rouwadvertenties.</p>

    <div class="stats stats-vertical lg:stats-horizontal shadow w-full border border-base-300">
        <div class="stat">
            <div class="stat-title">Totaal berichten</div>
            <div class="stat-value text-primary">{{ $notices->count() }}</div>
        </div>
        <div class="stat">
            <div class="stat-title">Overlijdensberichten</div>
            <div class="stat-value text-secondary">{{ $notices->where('type', 'overlijdensbericht')->count() }}</div>
        </div>
        <div class="stat">
            <div class="stat-title">Rouwadvertenties</div>
            <div class="stat-value">{{ $notices->where('type', 'rouwadvertentie')->count() }}</div>
        </div>
    </div>
@endsection
