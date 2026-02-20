@php($editing = isset($notice))
<form method="post" action="{{ $editing ? route('account.notices.update', $notice) : route('account.notices.store') }}" class="grid">
    @csrf
    @if($editing) @method('PUT') @endif

    <label>Titel
        <input name="title" value="{{ old('title', $notice->title ?? '') }}" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
    </label>

    <label>Type bericht
        <select name="type" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
            @foreach (['overlijdensbericht' => 'Overlijdensbericht', 'familiebericht' => 'Familiebericht', 'rouwadvertentie' => 'Rouwadvertentie'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $notice->type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <label>Voornaam overledene
            <input name="deceased_first_name" value="{{ old('deceased_first_name', $notice->deceased_first_name ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
        </label>
        <label>Achternaam overledene
            <input name="deceased_last_name" value="{{ old('deceased_last_name', $notice->deceased_last_name ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
        </label>
    </div>

    <label>Foto URL (ronde profielfoto op overzicht)
        <input type="url" name="photo_url" value="{{ old('photo_url', $notice->photo_url ?? '') }}" placeholder="https://..." style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
    </label>

    <label>Korte intro
        <textarea name="excerpt" rows="3" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">{{ old('excerpt', $notice->excerpt ?? '') }}</textarea>
    </label>

    <label>Berichttekst
        <textarea name="content" rows="9" required style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">{{ old('content', $notice->content ?? '') }}</textarea>
    </label>

    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <label>Provincie<input name="province" value="{{ old('province', $notice->province ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
        <label>Stad<input name="city" value="{{ old('city', $notice->city ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
        <label>Geboortedatum<input type="date" name="born_date" value="{{ old('born_date', isset($notice) && $notice->born_date ? $notice->born_date->format('Y-m-d') : '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
        <label>Overlijdensdatum<input type="date" name="died_date" value="{{ old('died_date', isset($notice) && $notice->died_date ? $notice->died_date->format('Y-m-d') : '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
    </div>

    <h3 style="margin-bottom:0;">Uitvaartondernemer</h3>
    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <label>Naam<input name="funeral_company_name" value="{{ old('funeral_company_name', $notice->funeral_company_name ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
        <label>Contactpersoon<input name="funeral_company_contact" value="{{ old('funeral_company_contact', $notice->funeral_company_contact ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
        <label>Telefoon<input name="funeral_company_phone" value="{{ old('funeral_company_phone', $notice->funeral_company_phone ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
        <label>E-mail<input type="email" name="funeral_company_email" value="{{ old('funeral_company_email', $notice->funeral_company_email ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;"></label>
    </div>

    <label>Condoleance URL (optioneel)
        <input type="url" name="condolence_url" value="{{ old('condolence_url', $notice->condolence_url ?? '') }}" style="width:100%;padding:10px;border:1px solid #d1d9de;border-radius:10px;">
    </label>

    @if($errors->any())<div style="color:#9e2b2b;">{{ $errors->first() }}</div>@endif

    <button type="submit" style="padding:11px 14px;background:#1e4c73;color:#fff;border:none;border-radius:10px;cursor:pointer;width:max-content;">{{ $editing ? 'Opslaan' : 'Publiceren' }}</button>
</form>
