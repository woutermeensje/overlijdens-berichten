{{-- Kaart van één organisatie; verwacht $organization. --}}
<article class="card bg-base-100 border border-base-300">
    <div class="card-body gap-3">
        <div class="space-y-1">
            <span class="badge badge-outline">{{ $organization->categoryLabel() }}</span>
            <h2 class="text-lg font-semibold leading-snug">{{ $organization->name }}</h2>
            <p class="text-sm text-base-content/70">
                {{ $organization->address ? $organization->address.', ' : '' }}{{ $organization->city }}@if($organization->province) · {{ $organization->province }}@endif
            </p>
        </div>

        <p class="text-sm text-base-content/80 flex-1">{{ \Illuminate\Support\Str::limit($organization->about, 220) }}</p>

        <ul class="text-sm space-y-1">
            @if($organization->website)
                <li><a href="{{ $organization->website }}" class="link link-hover break-all" target="_blank" rel="noopener nofollow">{{ $organization->websiteLabel() }}</a></li>
            @endif
            <li><a href="mailto:{{ $organization->email }}" class="link link-hover break-all">{{ $organization->email }}</a></li>
            @if($organization->phone)
                <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $organization->phone) }}" class="link link-hover">{{ $organization->phone }}</a></li>
            @endif
        </ul>
    </div>
</article>
