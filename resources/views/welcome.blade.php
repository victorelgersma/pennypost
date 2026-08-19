<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <title>{{ config('app.name', 'Penny Post') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|fraunces:400,500,600,600i|space-mono:400,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root{
                --paper:#F3EEE2;
                --paper-card:#FBF8F1;
                --ink:#211D18;
                --ink-soft:#5B564C;
                --stamp-red:#AF3029;
                --stamp-red-dark:#7C221D;
                --brass:#9C7A45;
                --line:#D8D0BE;
            }
            *{box-sizing:border-box;}
            html,body{margin:0;padding:0;}
            body{
                background:var(--paper);
                color:var(--ink);
                font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif;
                -webkit-font-smoothing:antialiased;
            }
            .pp-serif{font-family:'Fraunces',ui-serif,Georgia,serif;}
            .pp-mono{font-family:'Space Mono',ui-monospace,monospace;letter-spacing:.04em;}
            .pp-wrap{max-width:1040px;margin:0 auto;padding:0 24px;}

            .pp-nav{display:flex;align-items:center;justify-content:space-between;padding:28px 0;}
            .pp-brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--ink);}
            .pp-brand-mark{width:30px;height:30px;border-radius:50%;border:1.5px solid var(--stamp-red);display:flex;align-items:center;justify-content:center;color:var(--stamp-red);font-size:13px;}
            .pp-brand-name{font-size:19px;font-weight:600;}
            .pp-navlinks{display:flex;align-items:center;gap:8px;}
            .pp-btn{
                font-size:14px;padding:9px 18px;border-radius:2px;text-decoration:none;
                border:1px solid transparent;font-weight:500;display:inline-block;
            }
            .pp-btn-ghost{color:var(--ink);border-color:var(--line);}
            .pp-btn-ghost:hover{border-color:var(--ink);}
            .pp-btn-solid{background:var(--stamp-red);color:var(--paper-card);border-color:var(--stamp-red);}
            .pp-btn-solid:hover{background:var(--stamp-red-dark);border-color:var(--stamp-red-dark);}

            .pp-hero{display:grid;grid-template-columns:1.15fr .85fr;gap:48px;align-items:center;padding:56px 0 72px;}
            .pp-eyebrow{
                display:inline-flex;align-items:center;gap:8px;
                font-size:12px;color:var(--stamp-red);text-transform:uppercase;letter-spacing:.14em;font-weight:700;margin-bottom:18px;
            }
            .pp-eyebrow::before{content:"";width:16px;height:1px;background:var(--stamp-red);}
            .pp-h1{font-size:52px;line-height:1.05;margin:0 0 20px;font-weight:600;}
            .pp-h1 em{font-style:italic;color:var(--stamp-red);}
            .pp-lede{font-size:17px;line-height:1.65;color:var(--ink-soft);max-width:46ch;margin:0 0 32px;}
            .pp-cta-row{display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
            .pp-cta-note{font-size:13px;color:var(--ink-soft);}

            .pp-stamp-frame{
                background:var(--paper-card);
                border:1.5px solid var(--ink);
                position:relative;padding:26px;aspect-ratio:1/1.15;
                display:flex;align-items:center;justify-content:center;
            }
            .pp-stamp-frame::before{
                content:"";position:absolute;inset:8px;
                border:1px dashed var(--line);
            }
            .pp-postmark{width:78%;}
            .pp-postmark circle.ring{fill:none;stroke:var(--stamp-red);}
            .pp-postmark path.arc-text{fill:none;}
            .pp-postmark text{fill:var(--stamp-red);font-family:'Space Mono',monospace;}
            .pp-postmark .center-line{stroke:var(--stamp-red);}

            .pp-section{padding:56px 0;border-top:1px solid var(--line);}
            .pp-section-head{margin-bottom:36px;max-width:52ch;}
            .pp-kicker{font-size:12px;color:var(--brass);text-transform:uppercase;letter-spacing:.14em;font-weight:700;margin:0 0 10px;}
            .pp-h2{font-size:30px;margin:0;font-weight:600;}

            .pp-stamps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
            .pp-stamp-card{
                background:var(--paper-card);
                padding:26px 22px;position:relative;
                -webkit-mask-image:radial-gradient(circle 3px at 0 0, transparent 3px, black 3.5px), radial-gradient(circle 3px at 100% 0, transparent 3px, black 3.5px), radial-gradient(circle 3px at 0 100%, transparent 3px, black 3.5px), radial-gradient(circle 3px at 100% 100%, transparent 3px, black 3.5px);
                border:1px solid var(--line);
            }
            .pp-stamp-num{font-size:12px;color:var(--stamp-red);margin:0 0 14px;}
            .pp-stamp-title{font-size:17px;font-weight:600;margin:0 0 8px;}
            .pp-stamp-body{font-size:14px;line-height:1.55;color:var(--ink-soft);margin:0;}

            .pp-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:0;position:relative;}
            .pp-steps::before{
                content:"";position:absolute;top:11px;left:6%;right:6%;height:0;
                border-top:1px dashed var(--brass);
            }
            .pp-step{position:relative;padding-top:36px;}
            .pp-step-dot{
                width:22px;height:22px;border-radius:50%;background:var(--paper);
                border:1.5px solid var(--brass);position:absolute;top:0;left:0;
                display:flex;align-items:center;justify-content:center;
                font-size:11px;font-family:'Space Mono',monospace;color:var(--brass);
            }
            .pp-step-title{font-size:16px;font-weight:600;margin:0 0 6px;}
            .pp-step-body{font-size:14px;line-height:1.55;color:var(--ink-soft);margin:0;padding-right:18px;}

            .pp-rate{
                background:var(--ink);color:var(--paper);
                padding:44px;display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;
            }
            .pp-rate-left{max-width:38ch;}
            .pp-rate-title{font-size:26px;font-weight:600;margin:0 0 10px;}
            .pp-rate-body{font-size:14px;line-height:1.6;color:#C9C4B6;margin:0;}
            .pp-rate-denom{
                display:flex;flex-direction:column;align-items:center;justify-content:center;
                border:1.5px solid var(--paper);padding:16px 26px;text-align:center;flex-shrink:0;
            }
            .pp-rate-price{font-size:34px;font-weight:700;font-family:'Space Mono',monospace;line-height:1;}
            .pp-rate-unit{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#C9C4B6;margin-top:6px;}

            .pp-footer{padding:32px 0 48px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
            .pp-footer-text{font-size:13px;color:var(--ink-soft);}
            .pp-footer-links{display:flex;gap:20px;}
            .pp-footer-links a{font-size:13px;color:var(--ink-soft);text-decoration:none;}
            .pp-footer-links a:hover{color:var(--ink);text-decoration:underline;}

            @media (max-width:820px){
                .pp-hero{grid-template-columns:1fr;padding-top:32px;}
                .pp-h1{font-size:38px;}
                .pp-stamp-frame{max-width:280px;margin:0 auto;}
                .pp-stamps-grid{grid-template-columns:1fr;}
                .pp-steps{grid-template-columns:1fr;gap:24px;}
                .pp-steps::before{display:none;}
                .pp-rate{flex-direction:column;text-align:center;padding:32px 24px;}
                .pp-rate-left{max-width:none;}
            }
        </style>
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
                            <a href="{{ url('/dashboard') }}" class="pp-btn pp-btn-solid">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="pp-btn pp-btn-ghost">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="pp-btn pp-btn-solid">Get started</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </nav>

            <main>
                <section class="pp-hero">
                    <div>
                        <p class="pp-eyebrow">Letters, not messages</p>
                        <h1 class="pp-h1 pp-serif">One post<br>a week, <em>only</em>.</h1>
                        <p class="pp-lede">Penny Post is a place to write real letters to real people. There's no inbox to refresh — everything you write is held and delivered together, once a week. Slower by design, and better for it.</p>
                        <div class="pp-cta-row">
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="pp-btn pp-btn-solid">Write your first letter</a>
                            @endif
                            <a href="#how-it-works" class="pp-btn pp-btn-ghost">See how it works</a>
                        </div>
                    </div>

                    <div class="pp-stamp-frame">
                        <svg class="pp-postmark" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <circle class="ring" cx="100" cy="100" r="92" stroke-width="1.5"/>
                            <circle class="ring" cx="100" cy="100" r="78" stroke-width="1"/>
                            <path id="arcTop" class="arc-text" d="M 30 100 A 70 70 0 0 1 170 100"/>
                            <path id="arcBottom" class="arc-text" d="M 170 105 A 70 70 0 0 1 30 105"/>
                            <text font-size="11" letter-spacing="2">
                                <textPath href="#arcTop" startOffset="50%" text-anchor="middle">PENNY POST · ONCE A WEEK</textPath>
                            </text>
                            <text font-size="11" letter-spacing="2">
                                <textPath href="#arcBottom" startOffset="50%" text-anchor="middle">WRITTEN SLOW · SENT FRIDAY</textPath>
                            </text>
                            <line class="center-line" x1="42" y1="80" x2="158" y2="80" stroke-width="1"/>
                            <line class="center-line" x1="42" y1="120" x2="158" y2="120" stroke-width="1"/>
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
                            <p class="pp-stamp-body">Letters are personal by default. Just you, them, and the page — no group threads, no reply-all.</p>
                        </div>
                        <div class="pp-stamp-card">
                            <p class="pp-stamp-num pp-mono">02</p>
                            <h3 class="pp-stamp-title">One delivery day</h3>
                            <p class="pp-stamp-body">Everything mails together, once a week. Nothing to refresh in between.</p>
                        </div>
                        <div class="pp-stamp-card">
                            <p class="pp-stamp-num pp-mono">03</p>
                            <h3 class="pp-stamp-title">No pings, no badges</h3>
                            <p class="pp-stamp-body">Penny Post doesn't notify you the moment something arrives. It waits for post day, like mail should.</p>
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
                            <p class="pp-step-body">Write to someone whenever the moment strikes. Come back and add to it all week if you like.</p>
                        </div>
                        <div class="pp-step">
                            <div class="pp-step-dot pp-mono">2</div>
                            <h3 class="pp-step-title">Postmarked</h3>
                            <p class="pp-step-body">Once you seal it, it waits in the post room with everyone else's letters until post day.</p>
                        </div>
                        <div class="pp-step">
                            <div class="pp-step-dot pp-mono">3</div>
                            <h3 class="pp-step-title">Delivered</h3>
                            <p class="pp-step-body">Every Friday, the week's letters go out together. One quiet moment, not a constant drip.</p>
                        </div>
                    </div>
                </section>

                <section class="pp-section">
                    <div class="pp-rate">
                        <div class="pp-rate-left">
                            <h2 class="pp-rate-title pp-serif">Slow is the whole point.</h2>
                            <p class="pp-rate-body">A letter that arrives once a week gets read properly, and answered thoughtfully. Penny Post is built to be checked in on, not checked constantly — one less thing pulling at your attention.</p>
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
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}">Log in</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}">Get started</a>
                    @endif
                </div>
            </footer>
        </div>
    </body>
</html>
