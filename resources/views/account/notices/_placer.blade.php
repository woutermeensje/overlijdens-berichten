{{-- Contactgegevens van de plaatser; alleen voor admins. --}}
@if(auth()->user()->isAdmin())
    <div class="rounded-box border border-base-300 bg-base-200/40 p-4 mb-6">
        <h2 class="font-semibold mb-2">Geplaatst door</h2>
        @if($notice->placer_name || $notice->placer_email)
            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                <dt class="text-base-content/60">Naam</dt>
                <dd>{{ $notice->placer_name ?: '—' }}</dd>
                <dt class="text-base-content/60">E-mail</dt>
                <dd>
                    @if($notice->placer_email)
                        <a href="mailto:{{ $notice->placer_email }}" class="link">{{ $notice->placer_email }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt class="text-base-content/60">Telefoon</dt>
                <dd>
                    @if($notice->placer_phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $notice->placer_phone) }}" class="link">{{ $notice->placer_phone }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt class="text-base-content/60">Account</dt>
                <dd>{{ $notice->user?->email ?? '—' }}</dd>
            </dl>
        @else
            <p class="text-sm text-base-content/60">Geen contactgegevens bekend (geplaatst vóór het contactformulier of via de beheeromgeving).</p>
        @endif
    </div>
@endif
