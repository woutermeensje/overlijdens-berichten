@extends('layouts.public')

@section('title', 'Bericht plaatsen')

@section('content')
    @php
        $displayName = trim(($data['deceased_first_name'] ?? '').' '.($data['deceased_last_name'] ?? ''));
        $stepTitle = match ($step) {
            1 => 'Stap 1 van 3: Gegevens overledene',
            2 => 'Stap 2 van 3: Bericht en locatie',
            default => 'Stap 3 van 3: Controle en publicatie',
        };
        $stepDescription = match ($step) {
            1 => 'Vul de basisgegevens in en upload optioneel een foto.',
            2 => 'Kies het type bericht en voeg inhoud toe.',
            default => 'Controleer alles en publiceer direct.',
        };
    @endphp

    <section class="max-w-4xl mx-auto space-y-5">
        <article class="card bg-base-100 border border-base-300 shadow-md overflow-hidden">
            <div class="bg-base-200/60 border-b border-base-300 p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="space-y-2">
                        <h1 class="text-3xl font-semibold leading-tight">Bericht plaatsen</h1>
                        <p class="text-base-content/70">In 3 stappen plaats je gratis een overlijdensbericht, familiebericht of rouwadvertentie.</p>
                    </div>
                    <span class="badge badge-outline">Gratis pakket</span>
                </div>
                <ul class="steps w-full mt-5">
                    <li class="step {{ $step >= 1 ? 'step-primary' : '' }}">Gegevens</li>
                    <li class="step {{ $step >= 2 ? 'step-primary' : '' }}">Bericht</li>
                    <li class="step {{ $step >= 3 ? 'step-primary' : '' }}">Controle</li>
                </ul>
            </div>

            <div class="card-body space-y-6 p-6 md:p-8">
                <header class="space-y-1">
                    <h2 class="text-2xl font-semibold">{{ $stepTitle }}</h2>
                    <p class="text-base-content/70">{{ $stepDescription }}</p>
                </header>

                @if($errors->any())
                    <div class="alert alert-error shadow-sm">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="post" action="{{ route('notice.wizard.submit') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="step" value="{{ $step }}">

                    @if ($step === 1)
                        <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                            <h3 class="text-lg font-semibold">Persoonsgegevens</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="form-control w-full">
                                    <span class="label-text font-medium">Voornaam</span>
                                    <input class="input input-bordered w-full" name="deceased_first_name" value="{{ old('deceased_first_name', $data['deceased_first_name'] ?? '') }}" required>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text font-medium">Achternaam</span>
                                    <input class="input input-bordered w-full" name="deceased_last_name" value="{{ old('deceased_last_name', $data['deceased_last_name'] ?? '') }}" required>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="form-control w-full">
                                    <span class="label-text font-medium">Geboortedatum</span>
                                    <input type="date" class="input input-bordered w-full" name="born_date" value="{{ old('born_date', $data['born_date'] ?? '') }}" required>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text font-medium">Overlijdensdatum</span>
                                    <input type="date" class="input input-bordered w-full" name="died_date" value="{{ old('died_date', $data['died_date'] ?? '') }}" required>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                            <h3 class="text-lg font-semibold">Portretfoto</h3>
                            <label class="form-control w-full">
                                <span class="label-text font-medium">Foto (optioneel)</span>
                                <input type="file" class="file-input file-input-bordered w-full" name="photo" accept="image/jpeg,image/png,image/webp">
                                <span class="label-text-alt text-base-content/60">JPG, PNG of WEBP, maximaal 5MB</span>
                            </label>

                            @if(!empty($data['photo_url']))
                                <div class="flex items-center gap-3 rounded-box border border-base-300 bg-base-200 p-3">
                                    <div class="avatar">
                                        <div class="w-16 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                            <img src="{{ $data['photo_url'] }}" alt="Geuploade foto">
                                        </div>
                                    </div>
                                    <span class="text-sm text-base-content/70">Huidige geuploade foto</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($step === 2)
                        <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                            <h3 class="text-lg font-semibold">Type en locatie</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="form-control w-full md:col-span-2">
                                    <span class="label-text font-medium">Type bericht</span>
                                    <select name="type" class="select select-bordered w-full" required>
                                        @foreach (['overlijdensbericht' => 'Overlijdensbericht', 'familiebericht' => 'Familiebericht', 'rouwadvertentie' => 'Rouwadvertentie'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type', $data['type'] ?? '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="form-control w-full">
                                    <span class="label-text font-medium">Stad</span>
                                    <input class="input input-bordered w-full" name="city" value="{{ old('city', $data['city'] ?? '') }}" required>
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text font-medium">Provincie</span>
                                    <input class="input input-bordered w-full" name="province" value="{{ old('province', $data['province'] ?? '') }}" required>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                            <h3 class="text-lg font-semibold">Inhoud</h3>
                            <label class="form-control w-full">
                                <span class="label-text font-medium">Titel (optioneel)</span>
                                <input class="input input-bordered w-full" name="title" value="{{ old('title', $data['title'] ?? '') }}" placeholder="Laat leeg voor automatische naam">
                            </label>

                            <label class="form-control w-full">
                                <span class="label-text font-medium">Korte intro (optioneel)</span>
                                <textarea class="textarea textarea-bordered w-full" name="excerpt" rows="3">{{ old('excerpt', $data['excerpt'] ?? '') }}</textarea>
                            </label>

                            <label class="form-control w-full">
                                <span class="label-text font-medium">Berichttekst</span>
                                <textarea class="textarea textarea-bordered w-full min-h-44" name="content" rows="8" required>{{ old('content', $data['content'] ?? '') }}</textarea>
                            </label>
                        </div>
                    @endif

                    @if ($step === 3)
                        <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                            <h3 class="text-lg font-semibold">Samenvatting</h3>
                            <div class="stats stats-vertical md:stats-horizontal shadow-sm border border-base-300 w-full">
                                <div class="stat">
                                    <div class="stat-title">Naam</div>
                                    <div class="stat-value text-lg break-words">{{ $displayName }}</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-title">Locatie</div>
                                    <div class="stat-value text-lg">{{ $data['city'] }}, {{ $data['province'] }}</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-title">Periode</div>
                                    <div class="stat-value text-lg">{{ \Illuminate\Support\Carbon::parse($data['born_date'])->format('d-m-Y') }} - {{ \Illuminate\Support\Carbon::parse($data['died_date'])->format('d-m-Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-base-200 border border-base-300 shadow-sm">
                            <div class="card-body p-5 space-y-4">
                                <h3 class="text-lg font-semibold">Voorbeeldweergave</h3>
                                <div class="flex items-start gap-4">
                                    @if(!empty($data['photo_url']))
                                        <div class="avatar">
                                            <div class="w-16 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-2">
                                                <img src="{{ $data['photo_url'] }}" alt="Foto van {{ $displayName }}">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="space-y-2">
                                        <h2 class="card-title text-lg">{{ $data['title'] ?: $displayName }}</h2>
                                        @if(!empty($data['excerpt']))
                                            <p class="text-base-content/70">{{ $data['excerpt'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="prose max-w-none leading-relaxed">{!! nl2br(e($data['content'])) !!}</div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between items-center gap-3 pt-2 border-t border-base-300">
                        @if($step > 1)
                            <button type="submit" name="action" value="back" class="btn btn-ghost">Terug</button>
                        @else
                            <span></span>
                        @endif

                        @if($step < 3)
                            <button type="submit" name="action" value="next" class="btn btn-primary min-w-44">Verder naar stap {{ $step + 1 }}</button>
                        @else
                            <button type="submit" name="action" value="publish" class="btn btn-primary min-w-44">Bericht publiceren</button>
                        @endif
                    </div>
                </form>
            </div>
        </article>
    </section>
@endsection
