@extends('layouts.account')

@section('title', 'Reacties')

@section('content')
    <h1 class="text-2xl font-semibold mb-2">Reacties</h1>
    <p class="text-base-content/70 mb-4">Alle condoleances die bezoekers bij overlijdensberichten hebben geplaatst, nieuwste eerst.</p>

    @if(session('status'))
        <div role="alert" class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="stats stats-vertical sm:stats-horizontal w-full border border-base-300 mb-6">
        <div class="stat">
            <div class="stat-title">Reacties totaal</div>
            <div class="stat-value text-primary">{{ $total }}</div>
        </div>
        <div class="stat">
            <div class="stat-title">Afgelopen 7 dagen</div>
            <div class="stat-value">{{ $lastWeek }}</div>
        </div>
    </div>

    <form method="GET" action="{{ route('account.condolences') }}" class="flex flex-col sm:flex-row gap-2 mb-4">
        <input type="search" name="zoek" value="{{ $search }}" class="input input-bordered w-full sm:flex-1"
               placeholder="Zoek op naam, e-mailadres, tekst of overledene">
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Zoeken</button>
            @if($search !== '')
                <a href="{{ route('account.condolences') }}" class="btn btn-ghost">Wissen</a>
            @endif
        </div>
    </form>

    @if($search !== '')
        <p class="text-sm text-base-content/70 mb-3">{{ $condolences->total() }} {{ $condolences->total() === 1 ? 'reactie' : 'reacties' }} gevonden voor "{{ $search }}"</p>
    @endif

    <div class="space-y-3">
        @forelse($condolences as $condolence)
            <article class="border border-base-300 rounded-box bg-base-100 p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-semibold">{{ trim($condolence->first_name.' '.$condolence->last_name) }}</div>
                        <a href="mailto:{{ $condolence->email }}" class="text-sm link link-hover break-all">{{ $condolence->email }}</a>
                    </div>
                    <div class="text-sm text-base-content/70 sm:text-right shrink-0">
                        <div>{{ $condolence->created_at?->timezone('Europe/Amsterdam')->format('d-m-Y H:i') }}</div>
                        @if($condolence->notice)
                            <div>
                                bij
                                <a href="{{ route('notice.show', $condolence->notice->slug) }}#condoleances" class="link" target="_blank" rel="noopener">{{ $condolence->notice->title }}</a>
                                @if($condolence->notice->status !== 'published')
                                    <span class="badge badge-sm badge-ghost">offline</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="prose prose-sm max-w-none text-base-content/80">{!! $condolence->message !!}</div>

                <form method="POST" action="{{ route('account.condolences.destroy', $condolence) }}" class="text-right"
                      onsubmit="return confirm('Weet je zeker dat je deze reactie wilt verwijderen? Dit kan niet ongedaan worden gemaakt.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm text-error"><i class="ph ph-trash"></i> Verwijderen</button>
                </form>
            </article>
        @empty
            <div class="border border-base-300 rounded-box bg-base-100 p-6 text-center text-base-content/70">
                {{ $search !== '' ? 'Geen reacties gevonden.' : 'Er zijn nog geen reacties geplaatst.' }}
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $condolences->links() }}
    </div>
@endsection
