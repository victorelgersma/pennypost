<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <title>{{ config('app.name', 'Penny Post') }}</title>

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
            @if (Route::has('login'))
                <div class="pp-navlinks">
                    @auth
                        <a href="{{ route('correspondence.index') }}" class="pp-btn pp-btn-solid">My correspondence</a>
                    @else
                        <a href="{{ route('login') }}" class="pp-btn pp-btn-ghost">Log in</a>
                        <a href="{{ route('register') }}" class="pp-btn pp-btn-solid">Sign up</a>
                    @endauth
                </div>
            @endif

        </nav>

        @if (session('status') === 'account-deleted')
            <div class="pp-banner">
                {{ __('Your account and everything in it has been deleted.') }}
            </div>
        @endif

        <main>

            <section class="pp-hero">
                <div>
                    <p class="pp-eyebrow">Letters, not messages</p>
                    <h1 class="pp-h1 pp-serif">One post<br>a week, <em>only</em>.</h1>
                    <p class="pp-lede">Penny Post is a place to write real letters to real people. There's no inbox to
                        refresh — everything you write is held and delivered together, once a week. Slower by design,
                        and better for it.</p>
                    <div class="pp-cta-row">
                        <a href="{{ route('register') }}" class="pp-btn pp-btn-solid">Write your first letter</a>
                        <a href="#how-it-works" class="pp-btn pp-btn-ghost">See how it works</a>
                    </div>
                </div>

                <div class="pp-stamp-frame">
                    <svg class="pp-postmark" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <circle class="ring" cx="100" cy="100" r="92" stroke-width="1.5" />
                        <circle class="ring" cx="100" cy="100" r="78" stroke-width="1" />
                        <path id="arcTop" class="arc-text" d="M 30 100 A 70 70 0 0 1 170 100" />
                        <path id="arcBottom" class="arc-text" d="M 170 105 A 70 70 0 0 1 30 105" />
                        <text font-size="11" letter-spacing="2">
                            <textPath href="#arcTop" startOffset="50%" text-anchor="middle">PENNY POST · ONCE A WEEK
                            </textPath>
                        </text>
                        <text font-size="11" letter-spacing="2">
                            <textPath href="#arcBottom" startOffset="50%" text-anchor="middle">WRITTEN SLOW · SENT
                                FRIDAY</textPath>
                        </text>
                        <line class="center-line" x1="42" y1="80" x2="158" y2="80" stroke-width="1" />
                        <line class="center-line" x1="42" y1="120" x2="158" y2="120" stroke-width="1" />
                        <text x="100" y="105" text-anchor="middle" font-size="22" font-weight="700">FRI</text>
                    </svg>
                </div>
            </section>

            <section class="pp-section">
                <div class="pp-section-head">
                    <p class="pp-kicker">Why it's different</p>
                    <h2 class="pp-h2 pp-serif">Not another inbox to check</h2>
                </div>
                <div class="pp-stamps-grid">
                    <div class="pp-stamp-card">
                        <p class="pp-stamp-num pp-mono">01</p>
                        <h3 class="pp-stamp-title">Write to one person</h3>
                        <p class="pp-stamp-body">Letters are personal by default. Just you, them, and the page — no
                            group threads, no reply-all.</p>
                    </div>
                    <div class="pp-stamp-card">
                        <p class="pp-stamp-num pp-mono">02</p>
                        <h3 class="pp-stamp-title">One delivery day</h3>
                        <p class="pp-stamp-body">Everything mails together, once a week. Nothing to refresh in between.
                        </p>
                    </div>
                    <div class="pp-stamp-card">
                        <p class="pp-stamp-num pp-mono">03</p>
                        <h3 class="pp-stamp-title">No pings, no badges</h3>
                        <p class="pp-stamp-body">Penny Post doesn't notify you the moment something arrives. It waits
                            for post day, like mail should.</p>
                    </div>
                </div>
            </section>

            <section class="pp-section" id="how-it-works">
                <div class="pp-section-head">
                    <p class="pp-kicker">How it works</p>
                    <h2 class="pp-h2 pp-serif">From draft to doorstep in three stops</h2>
                </div>
                <div class="pp-steps">
                    <div class="pp-step">
                        <div class="pp-step-dot pp-mono">1</div>
                        <h3 class="pp-step-title">Draft</h3>
                        <p class="pp-step-body">Write to someone whenever the moment strikes. Come back and add to it
                            all week if you like.</p>
                    </div>
                    <div class="pp-step">
                        <div class="pp-step-dot pp-mono">2</div>
                        <h3 class="pp-step-title">Postmarked</h3>
                        <p class="pp-step-body">Once you seal it, it waits in the post room with everyone else's letters
                            until post day.</p>
                    </div>
                    <div class="pp-step">
                        <div class="pp-step-dot pp-mono">3</div>
                        <h3 class="pp-step-title">Delivered</h3>
                        <p class="pp-step-body">Every Friday, the week's letters go out together. One quiet moment, not
                            a constant drip.</p>
                    </div>
                </div>
            </section>

            <section class="pp-section">
                <div class="pp-rate">
                    <div class="pp-rate-left">
                        <h2 class="pp-rate-title pp-serif">Slow is the whole point.</h2>
                        <p class="pp-rate-body">A letter that arrives once a week gets read properly, and answered
                            thoughtfully. Penny Post is built to be checked in on, not checked constantly — one less
                            thing pulling at your attention.</p>
                    </div>
                    <div class="pp-rate-denom">
                        <span class="pp-rate-price pp-serif">1&times;</span>
                        <span class="pp-rate-unit">delivery, every week</span>
                    </div>
                </div>
            </section>
        </main>

        <footer class="pp-footer">
            <p class="pp-footer-text">&copy; {{ date('Y') }} Penny Post.</p>
            <div class="pp-footer-links">
                <a href="{{ route('about') }}">{{ __('About') }}</a>
                <a href="{{ route('login') }}">{{ __('Log in') }}</a>
            </div>
        </footer>

        <x-site-footer />
    </div>
</body>

</html>