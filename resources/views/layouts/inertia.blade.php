<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" href="{{ asset(app()->environment('local', 'stage') ? 'assets/images/favicon-gray.webp' : 'assets/images/favicon.webp') }}">
        <link rel="preload" as="image" importance="high" href="{{ asset('assets/images/ra-icon.webp') }}">
        <link rel="image_src" href="{{ asset('assets/images/ra-icon.webp') }}">
        <meta name="copyright" content="Copyright 2014-{{ date('Y') }}">
        <meta name="keywords" content="games, achievements, retro, emulator">
        <meta name="format-detection" content="telephone=no">
        <meta name="theme-color" content="#2C2E30">

        {{-- Scripts --}}
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx", 'resources/css/app.css'], config('vite.build_path'))
        @inertiaHead
    </head>

    <body class="font-sans antialiased" data-scheme data-theme>
        @inertia
    </body>
</html>
