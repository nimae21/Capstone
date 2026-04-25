<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Achilles') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['figtree', 'sans-serif'],
                    },
                    animation: {
                        'spin-slow': 'spin 8s linear infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'cinematic': 'cinematicMove 20s ease infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '50%': { transform: 'translateY(-15px) rotate(2deg)' },
                        },
                        cinematicMove: {
                            '0%, 100%': { transform: 'translate(0, 0)' },
                            '50%': { transform: 'translate(3%, 3%)' },
                        },
                    },
                },
            },
        }
    </script>

    <style>
        /* Custom smooth transitions */
        * {
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
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
</head>
<body class="font-sans antialiased">
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