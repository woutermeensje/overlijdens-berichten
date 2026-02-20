@extends('layouts.public')

@section('title', 'Overlijdensberichten')

@section('content')
    <section class="card" style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0 0 6px;">Laatste overlijdensberichten</h1>
                <p style="margin:0;color:#58656f;">Zoek op naam, voornaam of locatie.</p>
            </div>
            <a href="{{ route('notice.place') }}" style="padding:10px 14px;border-radius:10px;background:#193a59;color:#fff;text-decoration:none;">Overlijdensbericht plaatsen</a>
        </div>

        <form method="get" action="{{ route('home') }}" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Zoek op naam, voornaam of locatie"
                style="flex:1;min-width:250px;padding:12px 14px;border:1px solid #d4d9de;border-radius:10px;"
            >
            <button type="submit" style="padding:12px 16px;border:1px solid #193a59;background:#193a59;color:#fff;border-radius:10px;cursor:pointer;">Zoeken</button>
            @if($search !== '')
                <a href="{{ route('home') }}" style="padding:12px 16px;border:1px solid #d4d9de;background:#fff;color:#1e1e1e;border-radius:10px;text-decoration:none;">Reset</a>
            @endif
        </form>
    </section>

    <section class="notices-grid">
        @forelse($latestNotices as $notice)
            @php
                $displayName = trim(($notice->deceased_first_name ?? '').' '.($notice->deceased_last_name ?? ''));
                if ($displayName === '') {
                    $displayName = $notice->title;
                }
            @endphp

            <article class="notice-card">
                <div class="notice-card__head">
                    <span class="notice-card__location">{{ $notice->city ?: ($notice->province ?: 'Onbekende locatie') }}</span>
                </div>
                <div class="notice-card__body">
                    <div class="notice-card__avatar-wrap">
                        @if($notice->photo_url)
                            <img src="{{ $notice->photo_url }}" alt="Foto van {{ $displayName }}" class="notice-card__avatar">
                        @else
                            <div class="notice-card__avatar notice-card__avatar--placeholder"><i class="ph ph-user"></i></div>
                        @endif
                    </div>
                    <div>
                        <h2 class="notice-card__name">{{ $displayName }}</h2>
                        <div class="notice-card__dates">
                            <span>{{ $notice->born_date?->format('d-m-Y') ?: 'Onbekend' }}</span>
                            <span class="notice-card__sep">|</span>
                            <span>{{ $notice->died_date?->format('d-m-Y') ?: 'Onbekend' }}</span>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <article class="card">
                @if($search !== '')
                    Geen berichten gevonden voor "{{ $search }}".
                @else
                    Er zijn nog geen berichten gepubliceerd.
                @endif
            </article>
        @endforelse
    </section>

    <style>
        .notices-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .notice-card {
            background: linear-gradient(135deg, #f6f6f6, #ececec);
            border: 1px solid #d5d5d5;
            min-height: 178px;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .notice-card__head {
            display: flex;
            justify-content: flex-end;
        }

        .notice-card__location {
            font-size: 18px;
            font-style: italic;
            color: #444;
        }

        .notice-card__body {
            display: grid;
            grid-template-columns: 92px 1fr;
            gap: 12px;
            align-items: center;
        }

        .notice-card__avatar-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .notice-card__avatar {
            width: 84px;
            height: 84px;
            border-radius: 999px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.16);
        }

        .notice-card__avatar--placeholder {
            background: #dfe4ea;
            color: #8c97a3;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 36px;
        }

        .notice-card__name {
            margin: 0 0 8px;
            font-size: 22px;
            line-height: 1.12;
            font-weight: 500;
            color: #3a3a3a;
            overflow-wrap: anywhere;
        }

        .notice-card__dates {
            color: #4d5660;
            font-size: 16px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .notice-card__sep {
            color: #9aa3ab;
        }

        @media (max-width: 1100px) {
            .notices-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 700px) {
            .notices-grid { grid-template-columns: 1fr; }
            .notice-card__name { font-size: 20px; }
        }
    </style>
@endsection
