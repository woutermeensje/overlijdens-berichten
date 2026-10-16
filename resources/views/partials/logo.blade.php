<a href="{{ route('home') }}" class="btn btn-ghost gap-2 text-lg font-semibold {{ $class ?? '' }}" style="font-family: 'Work Sans', sans-serif;" @if($iconOnly ?? false) aria-label="overlijdens-berichten.nl" @endif>
    {{-- Zelfde kruis als public/favicon.svg --}}
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="{{ $iconClass ?? 'h-5 w-5' }} shrink-0" aria-hidden="true"><path fill="currentColor" d="M13 2h6v7h7v6h-7v15h-6V15H6V9h7z"/></svg>
    @unless($iconOnly ?? false)
        <span>overlijdens-berichten.nl</span>
    @endunless
</a>
