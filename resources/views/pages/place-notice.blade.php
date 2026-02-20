@extends('layouts.public')

@section('title', 'Overlijdensbericht plaatsen')

@section('content')
    <section class="card" style="margin-bottom:14px;">
        <h1 style="margin:0 0 8px;">Overlijdensbericht plaatsen</h1>
        <p style="margin:0;color:#5a6772;">Plaats hier een overlijdensbericht, familiebericht of rouwadvertentie.</p>
    </section>

    <section class="card" style="max-width:620px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0 0 6px;">Gratis pakket</h2>
                <p style="margin:0;color:#5a6772;">Momenteel is publiceren volledig gratis.</p>
            </div>
            <span style="display:inline-block;padding:8px 10px;border-radius:999px;border:1px solid #d9d9d7;background:#f7faf7;color:#1d6b2f;font-weight:600;">€ 0,00</span>
        </div>

        <ul style="margin:14px 0 18px;padding-left:20px;color:#2d3740;">
            <li>Publiceer overlijdensbericht, familiebericht of rouwadvertentie</li>
            <li>Bericht direct zichtbaar in het overzicht</li>
            <li>Zelf beheren via je account</li>
        </ul>

        <a href="{{ route('register') }}" style="display:inline-block;padding:12px 16px;border-radius:10px;background:#193a59;color:#fff;text-decoration:none;">Bericht plaatsen</a>
    </section>
@endsection
