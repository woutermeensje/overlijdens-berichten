<!doctype html>
<html lang="nl" data-theme="memorial">
<head>
    @php
        $configuredSiteName = trim((string) config('app.name', ''));
        $siteName = $configuredSiteName !== '' && $configuredSiteName !== 'Laravel'
            ? $configuredSiteName
            : 'overlijdens-berichten.nl';
        $defaultDescription = 'Overlijdensberichten, familieberichten en rouwadvertenties.';
        $pageTitle = trim($__env->yieldContent('title'));
        $title = $pageTitle !== '' ? $pageTitle.' | '.$siteName : $siteName;
        $description = trim($__env->yieldContent('meta_description')) ?: $defaultDescription;
        $robots = trim($__env->yieldContent('meta_robots'));

        if ($robots === '') {
            $robots = request()->hasAny(['q', 'type', 'locatie'])
                ? 'noindex,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1'
                : 'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1';
        }

        $canonicalUrl = trim($__env->yieldContent('canonical_url')) ?: url()->current();
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
        $ogImage = trim($__env->yieldContent('og_image')) ?: asset('favicon.ico');
        $twitterCard = trim($__env->yieldContent('twitter_card')) ?: 'summary';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="rL2igVYswJn8aNcCv9m8jOerGBKanqMJrygtW_aPxk8" />
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="author" content="{{ $siteName }}">

    <meta property="og:locale" content="nl_NL">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle !== '' ? $pageTitle : $siteName }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $pageTitle !== '' ? $pageTitle : $siteName }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @hasSection('structured_data')
        @yield('structured_data')
    @endif
    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daisyui@5" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <style>
        :root {
            --font-sans: "Work Sans", sans-serif;
        }

        html,
        body {
            font-family: var(--font-sans);
        }

        /* Geen box-shadows op het platform, ook niet vanuit daisyUI-componenten */
        [data-theme="memorial"] {
            --depth: 0;
        }
        *,
        *::before,
        *::after {
            box-shadow: none !important;
        }

        /* daisyUI 5 kent .form-control niet meer: label boven het veld, met ruimte ertussen */
        .form-control {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        /* Eén border-stijl voor het hele platform: 1px solid base-300, radius 5px */
        [data-theme="memorial"] {
            --radius-box: 5px;
            --radius-field: 5px;
            --radius-selector: 5px;
            --border: 1px;
        }
        [data-theme="memorial"] .input,
        [data-theme="memorial"] .select,
        [data-theme="memorial"] .textarea,
        [data-theme="memorial"] .file-input {
            border: 1px solid var(--color-base-300);
            border-radius: 5px;
        }

        [data-theme="memorial"] .btn-primary {
            background-color: #5a3a3a;
            border-color: #5a3a3a;
            color: #fff;
        }
        [data-theme="memorial"] .btn-primary:hover {
            background-color: #4a2f2f;
            border-color: #4a2f2f;
        }
        [data-theme="memorial"] .menu li > a.active,
        [data-theme="memorial"] .menu li > a:active {
            background-color: #f3e9e2;
            color: #4a2f2f;
        }
    </style>
</head>
<body class="min-h-screen bg-base-200 text-base-content flex flex-col">
    <header class="bg-base-100 border-b border-base-300">
        <div class="navbar max-w-6xl w-full mx-auto px-4 min-h-16">
            <div class="navbar-start">
                {{-- Mobiel: lege ruimte zodat het kruis precies in het midden staat. --}}
                <span class="w-12 lg:hidden" aria-hidden="true"></span>
                <div class="hidden lg:block">@include('partials.logo')</div>
            </div>
            <div class="navbar-center">
                <div class="lg:hidden">@include('partials.logo', ['iconOnly' => true, 'iconClass' => 'h-8 w-8'])</div>
                <ul class="menu menu-horizontal px-1 gap-1 hidden lg:flex">
                    <li><a href="{{ route('options') }}"><i class="ph ph-list-checks"></i> Opties</a></li>
                    <li><a href="{{ route('cities.index') }}"><i class="ph ph-map-pin-area"></i> Steden</a></li>
                    <li><a href="{{ route('notice.wizard') }}"><i class="ph ph-note-pencil"></i> Bericht plaatsen</a></li>
                </ul>
            </div>
            <div class="navbar-end gap-2">
                <div class="hidden lg:flex items-center gap-2">
                    <span class="h-6 w-px bg-base-300 mx-2" aria-hidden="true"></span>
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('account.dashboard') : route('account.notices.index') }}" class="btn btn-ghost text-base"><i class="ph ph-house"></i> Account</a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline text-base"><i class="ph ph-sign-out"></i> Uitloggen</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost text-base"><i class="ph ph-sign-in"></i> Inloggen</a>
                        <a href="{{ route('notice.wizard') }}" class="btn btn-primary text-base"><i class="ph ph-note-pencil"></i> Bericht plaatsen</a>
                    @endauth
                </div>
                <button type="button" class="btn btn-ghost btn-square btn-lg lg:hidden" aria-label="Menu openen" aria-controls="mobile-menu" aria-expanded="false" data-mobile-menu-open>
                    <i class="ph ph-list text-4xl" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- Mobiel menu: schuift van rechts naar binnen. --}}
    <div class="lg:hidden" data-mobile-menu>
        <div class="fixed inset-0 z-40 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300" data-mobile-menu-overlay></div>
        <nav id="mobile-menu" aria-label="Menu" aria-hidden="true" inert
             class="fixed inset-y-0 right-0 z-50 w-80 max-w-[85vw] bg-base-100 border-l border-base-300 flex flex-col translate-x-full transition-transform duration-300 ease-out">
            <div class="flex items-center justify-between px-4 min-h-16 border-b border-base-300">
                <span class="font-semibold text-lg">Menu</span>
                <button type="button" class="btn btn-ghost btn-square btn-lg" aria-label="Menu sluiten" data-mobile-menu-close>
                    <i class="ph ph-x text-3xl" aria-hidden="true"></i>
                </button>
            </div>
            <ul class="menu menu-lg w-full p-4 gap-1 text-lg">
                <li><a href="{{ route('home') }}"><i class="ph ph-house-simple"></i> Overlijdensberichten</a></li>
                <li><a href="{{ route('options') }}"><i class="ph ph-list-checks"></i> Opties</a></li>
                <li><a href="{{ route('cities.index') }}"><i class="ph ph-map-pin-area"></i> Steden</a></li>
                @auth
                    <li><a href="{{ auth()->user()->isAdmin() ? route('account.dashboard') : route('account.notices.index') }}"><i class="ph ph-house"></i> Account</a></li>
                @else
                    <li><a href="{{ route('login') }}"><i class="ph ph-sign-in"></i> Inloggen</a></li>
                @endauth
            </ul>
            <div class="mt-auto p-4 space-y-2 border-t border-base-300">
                <a href="{{ route('notice.wizard') }}" class="btn btn-primary btn-lg w-full"><i class="ph ph-note-pencil"></i> Bericht plaatsen</a>
                @auth
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-lg w-full"><i class="ph ph-sign-out"></i> Uitloggen</button>
                    </form>
                @endauth
            </div>
        </nav>
    </div>
    <script>
        (() => {
            const root = document.querySelector('[data-mobile-menu]');
            const panel = root.querySelector('nav');
            const overlay = root.querySelector('[data-mobile-menu-overlay]');
            const openButton = document.querySelector('[data-mobile-menu-open]');

            const setOpen = (open) => {
                panel.classList.toggle('translate-x-full', !open);
                overlay.classList.toggle('opacity-0', !open);
                overlay.classList.toggle('pointer-events-none', !open);
                panel.toggleAttribute('inert', !open);
                panel.setAttribute('aria-hidden', String(!open));
                openButton.setAttribute('aria-expanded', String(open));
                document.documentElement.classList.toggle('overflow-hidden', open);
                (open ? root.querySelector('[data-mobile-menu-close]') : openButton).focus();
            };

            openButton.addEventListener('click', () => setOpen(true));
            overlay.addEventListener('click', () => setOpen(false));
            root.querySelector('[data-mobile-menu-close]').addEventListener('click', () => setOpen(false));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && openButton.getAttribute('aria-expanded') === 'true') setOpen(false);
            });
        })();
    </script>

    @hasSection('hero')
        @yield('hero')
    @endif

    <main class="max-w-6xl mx-auto w-full px-4 py-6 flex-1">
        @yield('content')
    </main>

    <footer class="bg-black text-white">
        <div class="max-w-6xl mx-auto w-full px-4 py-8">
            <h2 class="text-lg font-semibold mb-3">Steden</h2>
            @if(!empty($footerCities))
                <div class="flex flex-wrap gap-2">
                    @foreach($footerCities as $city)
                        <a
                            href="{{ route('city.show', ['city' => $city['slug']]) }}"
                            class="inline-flex items-center rounded-box border border-white/20 px-3 py-1.5 text-sm hover:bg-white hover:text-black transition-colors"
                        >
                            {{ $city['name'] }}
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-white/70 text-sm">Nog geen steden beschikbaar.</p>
            @endif
        </div>
    </footer>
</body>
</html>
