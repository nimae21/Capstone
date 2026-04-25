<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Achilles') }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Tailwind config override -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'cinematic': 'cinematicMove 20s ease infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '50%': { transform: 'translateY(-20px) rotate(2deg)' },
                        },
                        cinematicMove: {
                            '0%, 100%': { transform: 'translate(0, 0)' },
                            '50%': { transform: 'translate(5%, 5%)' },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Global Cinematic Styles -->
    <style>
        /* ---------- CINEMATIC BACKGROUND ANIMATIONS ---------- */
        @keyframes cinematicZoom {
            0% { transform: scale(1.08); filter: brightness(0.88) contrast(1.12); }
            100% { transform: scale(1.22); filter: brightness(0.92) contrast(1.18); }
        }
        .animate-cinematic-zoom {
            animation: cinematicZoom 22s infinite alternate ease-in-out;
        }

        @keyframes sweepGlide {
            0% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.2; }
            50% { transform: rotate(35deg) translateX(20%) translateY(20%); opacity: 0.5; }
            100% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.2; }
        }
        .animate-sweep-glide {
            animation: sweepGlide 14s infinite cubic-bezier(0.45, 0.05, 0.2, 0.99);
        }

        @keyframes breatheRed {
            0% { opacity: 0.1; transform: scale(1); }
            100% { opacity: 0.4; transform: scale(1.1); }
        }
        .animate-breathe-red {
            animation: breatheRed 9s infinite alternate;
        }

        @keyframes floatSlow {
            0% { transform: translate(0,0) scale(1); opacity: 0.08; }
            100% { transform: translate(20px, -25px) scale(1.3); opacity: 0.25; }
        }
        .particle-elegant {
            position: absolute;
            background: rgba(230,0,35,0.25);
            border-radius: 50%;
            filter: blur(3px);
            pointer-events: none;
            animation: floatSlow 15s infinite alternate;
        }

        @keyframes gridDrift {
            0% { transform: translate(0,0); }
            100% { transform: translate(45px, 45px); }
        }
        .animate-grid-drift {
            animation: gridDrift 24s infinite linear;
        }

        @keyframes gentleFloat {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }
        @keyframes entranceRise {
            from { opacity: 0; transform: translateY(30px) scale(0.96); filter: blur(4px); }
            to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
        }
        .glass-card-clean {
            animation: gentleFloat 5.5s infinite ease-in-out, entranceRise 0.8s ease-out 0.2s backwards;
            transition: box-shadow 0.3s ease;
        }

        @keyframes softGlow {
            0% { transform: scale(1); text-shadow: 0 0 0 rgba(230,0,35,0); }
            100% { transform: scale(1.03); text-shadow: 0 0 10px rgba(230,0,35,0.3); }
        }
        .animate-soft-glow {
            animation: softGlow 2.8s infinite alternate;
        }

        @keyframes gentleShake {
            0%,100% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-2px); }
            80% { transform: translateX(2px); }
        }

        @keyframes rippleClean {
            to { transform: scale(8); opacity: 0; }
        }
        .ripple-clean {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            transform: scale(0);
            animation: rippleClean 0.5s linear;
            pointer-events: none;
        }

        @keyframes lensFlare {
            0% { transform: translateX(-100%) rotate(25deg); opacity: 0; }
            20% { opacity: 0.4; }
            80% { opacity: 0.4; }
            100% { transform: translateX(200%) rotate(25deg); opacity: 0; }
        }
        .animate-lens-flare {
            animation: lensFlare 8s infinite ease-in-out;
        }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); opacity: 0.1; }
            50% { transform: translate(30px, -40px) scale(1.2); opacity: 0.3; }
            100% { transform: translate(-20px, 20px) scale(0.9); opacity: 0.1; }
        }
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(230,0,35,0.3), rgba(230,0,35,0));
            filter: blur(15px);
            pointer-events: none;
            animation: floatOrb 14s infinite alternate;
        }

        /* ---------- INPUT FIELDS (global styling) ---------- */
        .input-group-float {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .input-group-float input {
            width: 100%;
            padding: 1rem 1rem 0.5rem 1rem;
            background-color: white;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 40px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #1a1a1a;
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            outline: none;
        }
        .input-group-float input:focus {
            border-color: #E60023;
            box-shadow: 0 0 0 3px rgba(230,0,35,0.15), 0 0 0 6px rgba(230,0,35,0.05);
            transform: scale(1.01);
        }
        .input-group-float label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.9rem;
            color: #9a9fb0;
            pointer-events: none;
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            background: transparent;
            padding: 0 2px;
            font-weight: 500;
        }
        .input-group-float input:focus ~ label,
        .input-group-float input:not(:placeholder-shown) ~ label {
            top: 0.35rem;
            transform: translateY(0);
            font-size: 0.7rem;
            color: #E60023;
            font-weight: 700;
        }
        .input-spacer {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: #E60023;
            transition: width 0.35s ease, left 0.35s ease;
            border-radius: 2px;
            pointer-events: none;
        }
        .input-group-float input:focus ~ .input-spacer {
            width: calc(100% - 2rem);
            left: 1rem;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b0b4c4;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.2s;
            z-index: 2;
            opacity: 0;
            pointer-events: none;
        }
        .password-toggle.visible {
            opacity: 1;
            pointer-events: auto;
        }
        .password-toggle:hover { color: #E60023; }
        .input-group-float.error input {
            border-color: #E60023;
            background-color: #FFF8F8;
            animation: gentleShake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }
        .error-message {
            font-size: 0.68rem;
            color: #E60023;
            margin-top: 0.35rem;
            margin-left: 1rem;
            font-weight: 600;
        }

        /* Utilities */
        .backdrop-blur-16 { backdrop-filter: blur(16px); }
        .bg-gradient-radial { background-image: radial-gradient(circle, var(--tw-gradient-stops)); }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-5000 { animation-delay: 5s; }
    </style>

    <!-- Scripts -->
    @viteReactRefresh
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="relative bg-black font-['Inter',sans-serif] antialiased overflow-x-hidden">
    <!-- GLOBAL BACKGROUND LAYER (visible on all pages) -->
    <div class="fixed inset-0 z-0 overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=2000&auto=format')] bg-cover bg-center bg-no-repeat animate-cinematic-zoom" style="background-position: center 35%;"></div>
        <div class="absolute inset-0 bg-gradient-radial from-black/30 via-black/50 to-black/70"></div>
        <div class="absolute -top-[30%] -left-[30%] w-[160%] h-[160%] bg-gradient-to-br from-[#E60023]/8 via-transparent to-transparent rotate-[35deg] animate-sweep-glide pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-radial from-[#E60023]/10 via-transparent to-transparent animate-breathe-red pointer-events-none" style="background-position: 40% 60%;"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(230,0,35,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(230,0,35,0.015)_1px,transparent_1px)] bg-[length:45px_45px] pointer-events-none animate-grid-drift"></div>
        <div class="absolute inset-0 bg-gradient-radial from-transparent via-transparent to-black/60 pointer-events-none"></div>
        <div class="absolute top-1/4 left-0 w-[150%] h-[200%] bg-gradient-to-r from-transparent via-white/8 to-transparent rotate-[25deg] animate-lens-flare pointer-events-none"></div>
        <div class="floating-orb w-80 h-80 top-10 left-[10%]"></div>
        <div class="floating-orb w-96 h-96 bottom-20 right-[5%] animation-delay-2000"></div>
        <div class="floating-orb w-56 h-56 top-[40%] left-[80%] animation-delay-5000"></div>
        <div id="elegantParticles" class="absolute inset-0 pointer-events-none overflow-hidden"></div>
    </div>

    <!-- MAIN CONTENT -->
    <div id="app" class="relative z-10">
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Global Script for Particles -->
    <script>
        (function() {
            const particleField = document.getElementById('elegantParticles');
            if(particleField && particleField.children.length === 0) {
                for(let i = 0; i < 120; i++) {
                    const p = document.createElement('div');
                    p.classList.add('particle-elegant');
                    const size = Math.random() * 5 + 2;
                    p.style.width = `${size}px`;
                    p.style.height = `${size}px`;
                    p.style.left = `${Math.random() * 100}%`;
                    p.style.top = `${Math.random() * 100}%`;
                    p.style.animationDuration = `${Math.random() * 18 + 12}s`;
                    p.style.animationDelay = `${Math.random() * 10}s`;
                    particleField.appendChild(p);
                }
            }

            // Global parallax effect
            const bgShoe = document.querySelector('.animate-cinematic-zoom');
            if(bgShoe) {
                document.addEventListener('mousemove', (e) => {
                    const x = (e.clientX / window.innerWidth) * 20 - 10;
                    const y = (e.clientY / window.innerHeight) * 12 - 6;
                    bgShoe.style.transform = `translate(${x * -0.4}px, ${y * -0.3}px) scale(1.12)`;
                });
            }
        })();
    </script>
    
    @stack('scripts')
</body>
</html>