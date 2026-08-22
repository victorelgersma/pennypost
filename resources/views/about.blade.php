<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <title>{{ __('About') }} — {{ config('app.name', 'Penny Post') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|fraunces:400,500,600,600i|space-mono:400,700"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="pp-wrap">
        <nav class="pp-nav">
            <a href="{{ url('/') }}" class="pp-brand">
                <span class="pp-brand-mark">P</span>
                <span class="pp-brand-name pp-serif">Penny Post</span>
            </a>
            <div class="pp-navlinks">
                @auth
                    <a href="{{ route('correspondence.index') }}"
                        class="pp-btn pp-btn-solid">{{ __('My correspondence') }}</a>
                @else
                    <a href="{{ route('login') }}" class="pp-btn pp-btn-solid">{{ __('Get started') }}</a>
                @endauth
            </div>
        </nav>

        <main>
            <section class="pp-section" style="border-top: none; padding-top: 24px;">
                <p class="pp-eyebrow">About</p>
                <h1 class="pp-h1 pp-serif" style="font-size: 40px;">The idea behind Penny Post</h1>
                <p class="pp-lede">
                    {{ __("Penny Post aims to fix online communication by removing its most pernicious aspect — instantaneity.") }}
                </p>
                <p class="pp-lede">
                    {{ __("Modern messaging optimizes for speed: read receipts, typing indicators, notifications the second something lands. That speed comes at a cost — we write faster than we think, and we feel pressure to reply before we've had a chance to. Letters never worked that way. You wrote when you had something to say, sent it, and got on with your life until it arrived.") }}
                </p>
                <p class="pp-lede">
                    {{ __("Penny Post brings that rhythm online. Write to someone whenever the moment strikes. Once you seal a letter, it waits with everyone else's until the next delivery — every Friday at noon, all at once. No inbox to refresh in between.") }}
                </p>
            </section>

            <section class="pp-section">
                <div class="pp-section-head">
                    <p class="pp-kicker">Questions people ask</p>
                    <h2 class="pp-h2 pp-serif">A few things worth knowing</h2>
                </div>

                <div style="display: flex; flex-direction: column; gap: 28px; max-width: 60ch;">
                    <div>
                        <h3 class="pp-stamp-title">{{ __('Is Penny Post open source?') }}</h3>
                        <p class="pp-stamp-body">
                            {{ __('Yes — the full source is public on') }}
                            <a href="https://github.com/victorelgersma/pennypost" class="pp-link" target="_blank"
                                rel="noopener">GitHub</a>.
                        </p>
                    </div>

                    <div>
                        <h3 class="pp-stamp-title">{{ __('Is Penny Post end-to-end encrypted?') }}</h3>
                        <p class="pp-stamp-body">
                            {{ __("No. We looked into this seriously — real end-to-end encryption for a two-person exchange needs each letter encrypted separately to both the sender's and recipient's own keys, plus a real key-recovery story if someone loses access to a device. That's a meaningful ongoing commitment, and for personal letters between people who know each other, it didn't seem like the right tradeoff against the complexity it would add. Letters are stored securely, but Penny Post itself can technically access message content — the same as most email and messaging services.") }}
                        </p>
                    </div>

                    <div>
                        <h3 class="pp-stamp-title">{{ __('Who runs Penny Post?') }}</h3>
                        <p class="pp-stamp-body">
                        <p class="pp-stamp-body">
                            {{ __('Penny Post is a personal project actively developed and maintained by') }}
                            <a href="https://vjbe.net" class="pp-link" target="_blank" rel="noopener">Victor
                                Elgersma-Azmanov</a>
                        </p>
                        </p>
                    </div>
                </div>
            </section>

            <section class="pp-section">
                <div class="pp-section-head">
                    <p class="pp-kicker">Get in touch</p>
                    <h2 class="pp-h2 pp-serif">Questions, bugs, or feedback</h2>
                </div>
                <p class="pp-lede" style="margin-bottom: 0;">
                    {{ __('Email') }}
                    <a href="mailto:pennypost@vjbe.net" class="pp-link">pennypost@vjbe.net</a>
                    {{ __('or raise an issue on') }}
                    <a href="https://github.com/victorelgersma/pennypost" class="pp-link" target="_blank"
                        rel="noopener">GitHub</a>.
                </p>
            </section>
        </main>

        <x-site-footer />
    </div>
</body>

</html>