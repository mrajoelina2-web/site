<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('image/logo-icon.svg') }}">

        <title>{{ config('app.name', 'Équip\'Style') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="margin:0;">
        <x-video-bg />
        <x-toast />

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="{{ route('products.index') }}">
                    <img src="{{ asset('image/logo.jpeg') }}" alt="Équip'Style" style="height:80px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.4);">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-md overflow-hidden sm:rounded-lg"
                 style="background: rgba(255,255,255,0.95);">
                {{ $slot }}
            </div>

            <a href="{{ route('products.index') }}" style="color:#eae7dc; text-decoration:none; margin-top:18px; font-size:14px;">
                ← Retour au catalogue
            </a>
        </div>

        <x-password-toggle />
    </body>
</html>