<!DOCTYPE html>
<html lang="hu">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#00a640">
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
        <title>Periplast - Gépkezelő</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased">
        <livewire:production-dashboard />
        @livewireScripts
    </body>
</html>
