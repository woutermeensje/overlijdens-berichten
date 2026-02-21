@php($editing = isset($notice))
<form method="post" action="{{ $editing ? route('account.notices.update', $notice) : route('account.notices.store') }}" class="space-y-4">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="form-control w-full">
            <span class="label-text">Titel</span>
            <input name="title" value="{{ old('title', $notice->title ?? '') }}" required class="input input-bordered w-full" />
        </label>

        <label class="form-control w-full">
            <span class="label-text">Type bericht</span>
            <select name="type" required class="select select-bordered w-full">
                @foreach (['overlijdensbericht' => 'Overlijdensbericht', 'familiebericht' => 'Familiebericht', 'rouwadvertentie' => 'Rouwadvertentie'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $notice->type ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="form-control w-full">
            <span class="label-text">Voornaam overledene</span>
            <input name="deceased_first_name" value="{{ old('deceased_first_name', $notice->deceased_first_name ?? '') }}" class="input input-bordered w-full" />
        </label>
        <label class="form-control w-full">
            <span class="label-text">Achternaam overledene</span>
            <input name="deceased_last_name" value="{{ old('deceased_last_name', $notice->deceased_last_name ?? '') }}" class="input input-bordered w-full" />
        </label>
    </div>

    <label class="form-control w-full">
        <span class="label-text">Foto URL (ronde profielfoto op overzicht)</span>
        <input type="url" name="photo_url" value="{{ old('photo_url', $notice->photo_url ?? '') }}" placeholder="https://..." class="input input-bordered w-full" />
    </label>

    <label class="form-control w-full">
        <span class="label-text">Korte intro</span>
        <textarea name="excerpt" rows="3" class="textarea textarea-bordered w-full">{{ old('excerpt', $notice->excerpt ?? '') }}</textarea>
    </label>

    <label class="form-control w-full">
        <span class="label-text">Berichttekst</span>
        <textarea name="content" rows="9" required class="textarea textarea-bordered w-full">{{ old('content', $notice->content ?? '') }}</textarea>
    </label>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="form-control w-full"><span class="label-text">Provincie</span><input name="province" value="{{ old('province', $notice->province ?? '') }}" class="input input-bordered w-full"></label>
        <label class="form-control w-full"><span class="label-text">Stad</span><input name="city" value="{{ old('city', $notice->city ?? '') }}" class="input input-bordered w-full"></label>
        <label class="form-control w-full"><span class="label-text">Geboortedatum</span><input type="date" name="born_date" value="{{ old('born_date', isset($notice) && $notice->born_date ? $notice->born_date->format('Y-m-d') : '') }}" class="input input-bordered w-full"></label>
        <label class="form-control w-full"><span class="label-text">Overlijdensdatum</span><input type="date" name="died_date" value="{{ old('died_date', isset($notice) && $notice->died_date ? $notice->died_date->format('Y-m-d') : '') }}" class="input input-bordered w-full"></label>
    </div>

    <h3 class="text-lg font-semibold">Uitvaartondernemer</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="form-control w-full"><span class="label-text">Naam</span><input name="funeral_company_name" value="{{ old('funeral_company_name', $notice->funeral_company_name ?? '') }}" class="input input-bordered w-full"></label>
        <label class="form-control w-full"><span class="label-text">Contactpersoon</span><input name="funeral_company_contact" value="{{ old('funeral_company_contact', $notice->funeral_company_contact ?? '') }}" class="input input-bordered w-full"></label>
        <label class="form-control w-full"><span class="label-text">Telefoon</span><input name="funeral_company_phone" value="{{ old('funeral_company_phone', $notice->funeral_company_phone ?? '') }}" class="input input-bordered w-full"></label>
        <label class="form-control w-full"><span class="label-text">E-mail</span><input type="email" name="funeral_company_email" value="{{ old('funeral_company_email', $notice->funeral_company_email ?? '') }}" class="input input-bordered w-full"></label>
    </div>

    <label class="form-control w-full">
        <span class="label-text">Condoleance URL (optioneel)</span>
        <input type="url" name="condolence_url" value="{{ old('condolence_url', $notice->condolence_url ?? '') }}" class="input input-bordered w-full">
    </label>

    @if($errors->any())
        <div class="alert alert-error"><span>{{ $errors->first() }}</span></div>
    @endif

    <button type="submit" class="btn btn-primary">{{ $editing ? 'Opslaan' : 'Publiceren' }}</button>
</form>
