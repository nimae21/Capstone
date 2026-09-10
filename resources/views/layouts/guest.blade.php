<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Achilles') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('images/achilles logo foot.png') }}?v=3">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Tailwind CSS -->


    <style>
        :root {
            --app-height: 100vh;
        }

        @supports (height: 100dvh) {
            :root {
                --app-height: 100dvh;
            }
        }

        html, body {
            min-height: 100%;
            height: 100%;
        }

        body {
            min-height: var(--app-height);
        }

        /* Custom smooth transitions */
        * {
            transition-property: color, background-color, border-color, box-shadow;
            transition-duration: 0.2s;
        }
        
        /* Gradient text utility */
        .gradient-text {
            background: linear-gradient(135deg, #0f172a, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        /* Glass card utility */
        .glass-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
    
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/responsive.css') . '?v=' . filemtime(public_path('css/responsive.css')) }}">
    <script src="{{ asset('js/responsive.js') . '?v=' . filemtime(public_path('js/responsive.js')) }}" defer></script>
    @include('partials.customer-header-assets')
</head>
<body class="auth-site font-sans antialiased">
    @include('partials.customer-header')
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-gray-50 via-white to-gray-100 relative overflow-hidden">
        
        <!-- Animated background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gray-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        </div>
        
        <!-- Logo (optional – you can remove if you want) -->
        <div class="relative z-10">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500 hover:scale-105 transition-transform" />
            </a>
        </div>

        <!-- Content Card -->
        <div class="w-full sm:max-w-md mt-6 px-6 py-6 glass-card shadow-xl overflow-hidden sm:rounded-2xl relative z-10 hover:shadow-2xl transition-all">
            {{ $slot }}
        </div>
    </div>
</body>
</html>