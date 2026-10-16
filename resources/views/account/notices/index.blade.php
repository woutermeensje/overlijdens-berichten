@extends('layouts.account')

@section('title', 'Mijn berichten')

@section('content')
    <div class="flex justify-between items-center gap-3 mb-4">
        <h1 class="text-2xl font-semibold">Mijn berichten</h1>
        <a href="{{ auth()->user()->isAdmin() ? route('account.notices.create') : route('notice.wizard') }}" class="btn btn-primary">Nieuw bericht</a>
    </div>

    <div class="overflow-x-auto border border-base-300 rounded-box bg-base-100">
        <table class="table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Type</th>
                    @if(auth()->user()->isAdmin())
                        <th>Geplaatst door</th>
                    @endif
                    <th>Status</th>
                    <th>Datum</th>
                    <th class="text-right">Acties</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notices as $notice)
                    <tr>
                        <td>{{ $notice->title }}</td>
                        <td><span class="badge">{{ \App\Models\MemorialNotice::typeLabels()[$notice->type] ?? ucfirst($notice->type) }}</span></td>
                        @if(auth()->user()->isAdmin())
                            <td class="text-sm">
                                @if($notice->placer_name || $notice->placer_email)
                                    <div>{{ $notice->placer_name }}</div>
                                    <a href="mailto:{{ $notice->placer_email }}" class="link text-base-content/70">{{ $notice->placer_email }}</a>
                                @else
                                    <span class="text-base-content/50">—</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @if($notice->status === 'published')
                                <span class="badge badge-success badge-soft">Online</span>
                            @else
                                <span class="badge">Offline</span>
                            @endif
                        </td>
                        <td>{{ $notice->created_at->format('d-m-Y H:i') }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @if($notice->status === 'published')
                                    <a href="{{ route('notice.show', $notice->slug) }}" class="btn btn-sm btn-ghost" target="_blank" rel="noopener">Bekijk</a>
                                @endif
                                <a href="{{ route('account.notices.edit', $notice) }}" class="btn btn-sm">Bewerk</a>
                                <form method="post" action="{{ route('account.notices.status', $notice) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline">{{ $notice->status === 'published' ? 'Offline halen' : 'Online zetten' }}</button>
                                </form>
                                <form method="post" action="{{ route('account.notices.destroy', $notice) }}" onsubmit="return confirm('Weet je zeker dat je dit bericht wilt verwijderen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline btn-error">Verwijder</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}">Je hebt nog geen berichten geplaatst.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
