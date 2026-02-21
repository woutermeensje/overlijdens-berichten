@extends('layouts.public')

@section('title', $notice->title)

@section('content')
    @php
        $displayName = trim(($notice->deceased_first_name ?? '').' '.($notice->deceased_last_name ?? ''));
        if ($displayName === '') {
            $displayName = $notice->title;
        }
        $fallbackAvatar = 'https://ui-avatars.com/api/?name='.urlencode($displayName).'&background=e2e7ec&color=6b7580&size=256';
    @endphp

    <article class="card" style="width:min(1080px,100%);margin:0 auto;">
        <a href="{{ route('home') }}" style="text-decoration:none;color:#193a59;">&larr; Terug naar overzicht</a>

        <div style="display:grid;grid-template-columns:130px 1fr;gap:18px;align-items:center;margin-top:12px;">
            <div>
                @if($notice->photo_url)
                    <img src="{{ $notice->photo_url }}" alt="Foto van {{ $displayName }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';" style="width:120px;height:120px;border-radius:999px;object-fit:cover;border:4px solid #fff;box-shadow:0 1px 6px rgba(0,0,0,.16);">
                @else
                    <div style="width:120px;height:120px;border-radius:999px;background:#e2e7ec;color:#8f99a3;display:flex;justify-content:center;align-items:center;font-size:42px;"><i class="ph ph-user"></i></div>
                @endif
            </div>

            <div>
                <h1 style="margin:0 0 8px;">{{ $displayName }}</h1>
                <div style="display:flex;gap:10px;flex-wrap:wrap;color:#4d5a65;">
                    <span><i class="ph ph-calendar-blank"></i> {{ $notice->born_date?->format('d-m-Y') ?: 'Onbekend' }} - {{ $notice->died_date?->format('d-m-Y') ?: 'Onbekend' }}</span>
                    <span><i class="ph ph-map-pin"></i> {{ $notice->city ?: 'Onbekend' }}@if($notice->province), {{ $notice->province }}@endif</span>
                </div>
            </div>
        </div>

        <hr style="margin:16px 0;border:none;border-top:1px solid #dbe0e4;">

        <div style="line-height:1.65;color:#25313b;">
            {!! $notice->content !!}
        </div>
    </article>
@endsection
