@extends('layouts.account')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-semibold mb-2">Dashboard</h1>
    <p class="text-base-content/70 mb-4">Beheer je overlijdensberichten, familieberichten en rouwadvertenties.</p>

    <div class="stats stats-vertical lg:stats-horizontal w-full border border-base-300">
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
        <div class="stat">
            <div class="stat-title">Views afgelopen {{ $viewPeriodDays }} dagen</div>
            <div class="stat-value">{{ number_format($totalViews, 0, ',', '.') }}</div>
        </div>
    </div>

    <h2 class="text-xl font-semibold mt-8 mb-2">Views per bericht</h2>
    <p class="text-base-content/70 mb-4">Totaal aantal weergaven per bericht over de afgelopen {{ $viewPeriodDays }} dagen, meeste eerst. Bezoeken van admins en zoekmachines tellen niet mee.</p>

    @if($noticeViews->isEmpty())
        <div class="border border-base-300 rounded-box bg-base-100 p-6 text-center text-base-content/70">
            Er zijn nog geen views geregistreerd in deze periode.
        </div>
    @else
        <div class="overflow-x-auto border border-base-300 rounded-box bg-base-100">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bericht</th>
                        <th class="hidden sm:table-cell">Plaats</th>
                        <th class="text-right">Views</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($noticeViews as $notice)
                        <tr>
                            <td>
                                @if($notice->status === 'published')
                                    <a href="{{ route('notice.show', $notice->slug) }}" class="link link-hover" target="_blank" rel="noopener">{{ $notice->title }}</a>
                                @else
                                    {{ $notice->title }}
                                    <span class="badge badge-sm badge-ghost">offline</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-base-content/70">{{ $notice->city }}</td>
                            <td class="text-right tabular-nums font-semibold">{{ number_format((int) $notice->recent_views, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
