{{--
    Eén gebied in het alerts-formulier: soort (stad/provincie) + de keuze zelf.
    Alleen het veld dat bij de gekozen soort hoort is ingeschakeld, dus er wordt maar één areas[i][name] verstuurd.
--}}
@php
    $isProvince = ($area['type'] ?? 'city') === 'province';
@endphp
<div class="flex gap-2 items-start" data-area-row>
    <select name="areas[{{ $index }}][type]" class="select select-bordered w-32 shrink-0" aria-label="Soort gebied" data-area-type>
        <option value="city" @selected(! $isProvince)>Stad</option>
        <option value="province" @selected($isProvince)>Provincie</option>
    </select>

    <div class="relative flex-1 min-w-0 {{ $isProvince ? 'hidden' : '' }}" data-place-picker data-area-city>
        <input type="text" name="areas[{{ $index }}][name]" value="{{ $isProvince ? '' : ($area['name'] ?? '') }}" placeholder="Kies je stad"
               class="input input-bordered w-full px-3" autocomplete="off" role="combobox" aria-label="Stad" aria-autocomplete="list" aria-expanded="false"
               data-place-input @disabled($isProvince) />
        <input type="hidden" name="areas[{{ $index }}][id]" value="{{ $isProvince ? '' : ($area['id'] ?? '') }}" data-place-id @disabled($isProvince)>
        <ul role="listbox" class="hidden absolute z-30 left-0 right-0 mt-1 menu menu-sm bg-base-100 border border-base-300 rounded-box shadow-lg max-h-72 overflow-y-auto flex-nowrap" data-place-list></ul>
        <p class="hidden text-error text-xs mt-1 px-1" data-place-error>Kies een plaats uit de lijst met suggesties.</p>
    </div>

    <div class="flex-1 min-w-0 {{ $isProvince ? '' : 'hidden' }}" data-area-province>
        <select name="areas[{{ $index }}][name]" class="select select-bordered w-full" aria-label="Provincie" @disabled(! $isProvince)>
            <option value="" @selected(! $isProvince || empty($area['name']))>Kies je provincie</option>
            @foreach(\App\Models\RegionSubscription::PROVINCES as $province)
                <option value="{{ $province }}" @selected($isProvince && ($area['name'] ?? null) === $province)>{{ $province }}</option>
            @endforeach
        </select>
    </div>

    <button type="button" class="btn btn-ghost btn-square shrink-0" aria-label="Gebied verwijderen" data-area-remove>
        <i class="ph ph-x text-lg" aria-hidden="true"></i>
    </button>
</div>
