@extends('layouts.public')

@section('title', 'Bericht plaatsen')
@section('meta_description', 'Plaats gratis een overlijdensbericht, familiebericht of rouwadvertentie.')
@section('meta_robots', 'noindex,follow')

@section('content')
    <section class="max-w-4xl mx-auto space-y-5">
        <article class="card bg-base-100 border border-base-300 overflow-hidden">
            <div class="bg-base-200/60 border-b border-base-300 p-4 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="space-y-2">
                        <h1 class="text-2xl sm:text-3xl font-semibold leading-tight">Bericht plaatsen</h1>
                        <p class="text-sm sm:text-base text-base-content/70">Plaats gratis een overlijdensbericht, familiebericht of rouwadvertentie.</p>
                    </div>
                    <span class="badge badge-outline">Gratis pakket</span>
                </div>
            </div>

            <div class="card-body space-y-6 p-4 sm:p-6 md:p-8">
                @if($errors->any())
                    <div class="alert alert-error">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('notice.wizard.submit') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div style="display:none;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                        <label for="hp_website">Website</label>
                        <input type="text" id="hp_website" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                        <h2 class="text-lg font-semibold">Persoonsgegevens</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="form-control w-full">
                                <span class="label-text font-medium text-sm">Voornaam</span>
                                <input class="input input-bordered w-full" name="deceased_first_name" value="{{ old('deceased_first_name') }}" required>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium text-sm">Achternaam</span>
                                <input class="input input-bordered w-full" name="deceased_last_name" value="{{ old('deceased_last_name') }}" required>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="form-control w-full">
                                <span class="label-text font-medium text-sm">Geboortedatum</span>
                                <input type="date" class="input input-bordered w-full" name="born_date" value="{{ old('born_date') }}" required>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium text-sm">Overlijdensdatum</span>
                                <input type="date" class="input input-bordered w-full" name="died_date" value="{{ old('died_date') }}" required>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                        <h2 class="text-lg font-semibold">Portretfoto</h2>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Foto (optioneel)</span>
                            <input type="file" class="file-input file-input-bordered w-full" name="photo" accept="image/jpeg,image/png,image/webp">
                            <span class="label-text-alt text-base-content/60">JPG, PNG of WEBP, maximaal 5MB. Zonder foto tonen we een kaarsje.</span>
                        </label>
                    </div>

                    <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                        <h2 class="text-lg font-semibold">Type en locatie</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="form-control w-full md:col-span-2">
                                <span class="label-text font-medium text-sm">Type bericht</span>
                                <select name="type" class="select select-bordered w-full" required>
                                    @foreach (\App\Models\MemorialNotice::typeLabels() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-control w-full">
                                <span class="label-text font-medium text-sm">Stad</span>
                                <input class="input input-bordered w-full" name="city" value="{{ old('city') }}" required>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium text-sm">Provincie</span>
                                <input class="input input-bordered w-full" name="province" value="{{ old('province') }}" required>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-box border border-base-300 bg-base-100 p-4 md:p-5 space-y-4">
                        <h2 class="text-lg font-semibold">Inhoud</h2>
                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Titel (optioneel)</span>
                            <input class="input input-bordered w-full" name="title" value="{{ old('title') }}" placeholder="Laat leeg voor automatische naam">
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Korte intro (optioneel)</span>
                            <textarea class="textarea textarea-bordered w-full" name="excerpt" rows="3">{{ old('excerpt') }}</textarea>
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text font-medium text-sm">Berichttekst</span>
                            <div class="bg-white rounded-box border border-base-300 overflow-hidden">
                                <div id="content-editor" style="min-height: 11rem;">{!! \App\Support\NoticeContentSanitizer::clean((string) old('content', '')) !!}</div>
                            </div>
                            <textarea name="content" id="content-input" class="hidden">{{ old('content') }}</textarea>
                        </label>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-base-300">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto sm:min-w-44">Bericht publiceren</button>
                    </div>
                </form>
            </div>
        </article>
    </section>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        (function () {
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean'],
            ];

            var quill = new Quill('#content-editor', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Schrijf hier de tekst van het overlijdensbericht...',
            });

            var hiddenInput = document.getElementById('content-input');

            quill.on('text-change', function () {
                hiddenInput.value = quill.root.innerHTML;
            });

            hiddenInput.closest('form').addEventListener('submit', function () {
                hiddenInput.value = quill.root.innerHTML;
            });
        })();
    </script>
@endsection
