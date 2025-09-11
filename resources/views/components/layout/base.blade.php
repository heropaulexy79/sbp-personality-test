<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $tag_id = config('goole-analytics.tag_id');
@endphp

<head>
    @if (!empty($tag_id))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $tag_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $tag_id }}'); // Quotes are important here!
    </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    {{-- <link rel="icon" type="image/x-icon" href="/favicon.ico" /> --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}" />
    @vite('resources/css/app.css')
</head>

<body class="font-sans antialiased">
    <x-layout.header />
    {{ $slot }}
    <x-layout.footer />
</body>

</html>
