@extends('layouts.account')

@section('title', 'Mijn berichten')

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
        <h1 style="margin:0;">Mijn berichten</h1>
        <a href="{{ route('account.notices.create') }}" style="padding:10px 12px;border-radius:10px;background:#1e4c73;color:#fff;text-decoration:none;">Nieuw bericht</a>
    </div>

    <div class="grid" style="margin-top:14px;">
        @forelse($notices as $notice)
            <article style="border:1px solid #dde1e6;border-radius:12px;padding:12px;display:flex;justify-content:space-between;gap:10px;">
                <div>
                    <strong>{{ $notice->title }}</strong>
                    <div style="font-size:13px;color:#61707b;">{{ ucfirst($notice->type) }} • {{ $notice->created_at->format('d-m-Y H:i') }}</div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <a href="{{ route('account.notices.edit', $notice) }}" style="padding:8px 10px;border-radius:9px;border:1px solid #d2dae0;text-decoration:none;color:#1e2630;">Bewerk</a>
                    <form method="post" action="{{ route('account.notices.destroy', $notice) }}" onsubmit="return confirm('Weet je zeker dat je dit bericht wilt verwijderen?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="padding:8px 10px;border-radius:9px;border:1px solid #d2dae0;background:#fff;cursor:pointer;">Verwijder</button>
                    </form>
                </div>
            </article>
        @empty
            <p>Je hebt nog geen berichten geplaatst.</p>
        @endforelse
    </div>
@endsection
