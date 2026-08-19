<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Penny Post') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|fraunces:400,500,600,600i|space-mono:400,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <a href="/" class="flex items-center gap-2 mb-6">
                <span class="pp-brand-mark">P</span>
                <span class="pp-serif font-semibold text-lg" style="color: var(--ink);">Penny Post</span>
            </a>

            <div class="w-full sm:max-w-md px-6 py-8 pp-letter-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>