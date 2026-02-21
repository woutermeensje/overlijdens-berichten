@extends('layouts.account')

@section('title', 'Mijn berichten')

@section('content')
    <div class="flex justify-between items-center gap-3 mb-4">
        <h1 class="text-2xl font-semibold">Mijn berichten</h1>
        <a href="{{ route('account.notices.create') }}" class="btn btn-primary">Nieuw bericht</a>
    </div>

    <div class="overflow-x-auto border border-base-300 rounded-box bg-base-100">
        <table class="table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Type</th>
                    <th>Datum</th>
                    <th class="text-right">Acties</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notices as $notice)
                    <tr>
                        <td>{{ $notice->title }}</td>
                        <td><span class="badge">{{ ucfirst($notice->type) }}</span></td>
                        <td>{{ $notice->created_at->format('d-m-Y H:i') }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('account.notices.edit', $notice) }}" class="btn btn-sm">Bewerk</a>
                                <form method="post" action="{{ route('account.notices.destroy', $notice) }}" onsubmit="return confirm('Weet je zeker dat je dit bericht wilt verwijderen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline btn-error">Verwijder</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Je hebt nog geen berichten geplaatst.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
