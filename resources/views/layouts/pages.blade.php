<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Achilles') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/achilles logo foot.png') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        footer .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            font-size: 1.8rem;
            cursor: pointer;
            text-decoration: none;
            color: #0a0a0f;
            flex-shrink: 0;
        }
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #ffffff;
            color: #0a0a0f;
            line-height: 1.5;
            min-height: var(--app-height);
        }
        
        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 2rem;
            border-radius: 60px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            border: none;
            text-decoration: none;
        }
        
        .btn-red {
            background: #e53e3e;
            color: white;
            box-shadow: 0 8px 18px -6px rgba(229,62,62,0.5);
        }
        .btn-red:hover {
            background: #c53030;
            transform: translateY(-3px);
        }
        
        .btn-outline {
            background: transparent;
            border: 1.5px solid #0a0a0f;
            color: #0a0a0f;
        }
        .btn-outline:hover {
            background: #0a0a0f;
            color: white;
        }
        
        /* ── Container ── */
        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* ── Hero ── */
        .hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 4rem 2rem 5rem;
            gap: 3rem;
        }
        .hero-content { flex: 1 1 45%; }
        .hero-badge {
            display: inline-block;
            background: rgba(229,62,62,0.12);
            color: #e53e3e;
            padding: 0.4rem 1.2rem;
            border-radius: 40px;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .hero-content h1 {
            font-size: 4.2rem;
            font-weight: 800;
            line-height: 1.05;
            text-transform: uppercase;
        }
        .hero-features {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        .hero-image {
            flex: 1 1 45%;
            display: flex;
            justify-content: center;
        }
        .hero-image img {
            width: 100%;
            max-width: 550px;
            border-radius: 48px;
            box-shadow: 0 30px 40px -15px rgba(0,0,0,0.2);
        }
        
        /* ── Features ── */
        .features {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 1.8rem;
            padding: 3rem 2rem;
            background: #fafafc;
            border-radius: 80px;
            margin: 2rem auto;
        }
        .feature-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 40px;
            background: white;
            transition: 0.3s;
        }
        .feature-item i {
            font-size: 2.5rem;
            color: #e53e3e;
            margin-bottom: 0.8rem;
        }
        .feature-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(229,62,62,0.2);
        }
        
        /* ── Category Grid ── */
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 1.8rem;
            margin-bottom: 3rem;
        }
        .category-card {
            position: relative;
            border-radius: 36px;
            overflow: hidden;
            aspect-ratio: 1/1;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 15px 25px -10px rgba(0,0,0,0.1);
            text-decoration: none;
        }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }
        .category-card h3 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, #000, transparent);
            color: white;
            padding: 2rem 1.5rem 1.2rem;
            font-size: 1.8rem;
            font-weight: 700;
        }
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 40px -12px #e53e3e;
        }
        .category-card:hover img { transform: scale(1.08); }
        
        /* ── Category Pages ── */
        .category-header {
            padding: 2rem 2rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .category-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 2.8rem;
            font-weight: 800;
        }
        .category-title i { color: #e53e3e; font-size: 3rem; }
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 2rem;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(12px);
            border-radius: 80px;
            margin: 1rem 2rem 2rem;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .filter-tabs {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .filter-tag {
            background: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            border: 1px solid #eaeaea;
            cursor: pointer;
            transition: 0.2s;
        }
        .filter-tag i { margin-right: 8px; color: #e53e3e; }
        .filter-tag.active, .filter-tag:hover {
            background: #e53e3e;
            color: white;
            border-color: #e53e3e;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
            gap: 2rem;
            padding: 2rem 2rem 4rem;
        }
        
        .shoe-card {
            background: white;
            border-radius: 36px;
            padding: 1.5rem;
            transition: all 0.3s;
            border: 1px solid #f0f0f0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.02);
        }
        .shoe-card:hover {
            transform: translateY(-8px);
            border-color: rgba(229,62,62,0.3);
            box-shadow: 0 20px 30px -12px rgba(229,62,62,0.15);
        }
        .shoe-image {
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 28px;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .shoe-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: #e53e3e;
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 30px;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .price {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0.5rem 0 1rem;
            color: #e53e3e;
        }
        .price small {
            font-size: 0.8rem;
            color: #888;
            text-decoration: line-through;
            margin-left: 0.5rem;
        }
        
        /* ── CTA ── */
        .cta-banner {
            background: linear-gradient(135deg, #0a0a0f, #1a1a2a);
            border-radius: 60px;
            margin: 2rem 2rem 4rem;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
        }
        
        /* ── Footer ── */
        footer {
            background: #f8f9fc;
            border-top: 1px solid #eceef2;
            padding: 3rem 2rem 2rem;
            margin-top: 2rem;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2.5rem;
        }
        .social-icons {
            display: flex;
            gap: 1rem;
            font-size: 1.5rem;
            margin-top: 1rem;
        }
        .social-icons i { cursor: pointer; transition: 0.2s; }
        .social-icons i:hover { color: #e53e3e; transform: translateY(-3px); }
        .footer-col ul {
            list-style: none;
            margin-top: 0.5rem;
        }
        .footer-col ul li {
            margin-bottom: 0.4rem;
        }
        .footer-col ul li a {
            text-decoration: none;
            color: #555;
            transition: 0.2s;
        }
        .footer-col ul li a:hover {
            color: #e53e3e;
        }
        .copyright {
            text-align: center;
            padding-top: 2rem;
            font-size: 0.9rem;
            color: #888;
        }
        @media (max-width: 900px) {
            .features, .category-grid { grid-template-columns: repeat(2,1fr); }
            .hero-content h1 { font-size: 2.8rem; }
        }
        @media (max-width: 600px) {
            .hero-content h1 { font-size: 2.2rem; }
            .features {
                grid-template-columns: 1fr 1fr;
                border-radius: 40px;
                padding: 1.5rem;
            }
        }

        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            background: rgba(10, 10, 15, 0.72);
            backdrop-filter: blur(8px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .page-loader.is-visible { opacity: 1; pointer-events: auto; }
        .page-loader-content {
            display: grid;
            justify-items: center;
            gap: 1rem;
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .page-loader-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            animation: pageLoaderPulse 1.2s ease-in-out infinite;
        }
        .page-loader-ring {
            width: 34px;
            height: 34px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #e60023;
            border-radius: 50%;
            animation: pageLoaderSpin 0.8s linear infinite;
        }
        @keyframes pageLoaderPulse {
            0%, 100% { transform: scale(0.94); opacity: 0.72; }
            50% { transform: scale(1); opacity: 1; }
        }
        @keyframes pageLoaderSpin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .page-loader-logo, .page-loader-ring { animation: none; }
        }
    </style>
    
    @yield('styles')
    <style>
        .product-grid .shoe-card {
            display: flex;
            flex-direction: column;
        }
        .product-grid .shoe-card .product-card-price {
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.5;
            color: #dc2626;
            margin: auto 1rem 0.75rem;
            padding-top: 0.25rem;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/responsive.css') . '?v=' . filemtime(public_path('css/responsive.css')) }}">
    <script src="{{ asset('js/responsive.js') . '?v=' . filemtime(public_path('js/responsive.js')) }}" defer></script>
    @include('partials.customer-header-assets')
</head>
<body class="store-site">
    <div id="pageLoader" class="page-loader" role="status" aria-live="polite" aria-label="Loading">
        <div class="page-loader-content">
            <img src="{{ asset('images/achilles logo foot.png') }}" alt="Achilles" class="page-loader-logo">
            <div class="page-loader-ring" aria-hidden="true"></div>
            <span>Loading</span>
        </div>
    </div>

    <!-- ════════════════════════════════════════ -->
    <!--  NAVIGATION                            -->
    <!-- ════════════════════════════════════════ -->
    @include('partials.customer-header')

    <!-- ════════════════════════════════════════ -->
    <!--  MAIN CONTENT                          -->
    <!-- ════════════════════════════════════════ -->
    <main>
        @yield('content')
    </main>

    <!-- ════════════════════════════════════════ -->
    <!--  FOOTER                                -->
    <!-- ════════════════════════════════════════ -->
    @include('partials.store-footer')

    <!-- ════════════════════════════════════════ -->
    <!--  SCRIPTS                               -->
    <!-- ════════════════════════════════════════ -->
    <script>
        (function() {
            const loader = document.getElementById('pageLoader');

            if (!loader) {
                return;
            }

            const showLoader = () => loader.classList.add('is-visible');

            document.addEventListener('click', function(event) {
                const link = event.target.closest('a');

                if (!link || event.defaultPrevented || link.target === '_blank' || link.hasAttribute('download')) {
                    return;
                }

                const href = link.getAttribute('href');

                if (!href || href.startsWith('#') || href.startsWith('javascript:') || new URL(link.href).origin !== window.location.origin) {
                    return;
                }

                showLoader();
            });

            document.addEventListener('submit', function(event) {
                if (!event.defaultPrevented) {
                    showLoader();
                }
            });

            window.addEventListener('pageshow', () => loader.classList.remove('is-visible'));
        })();


    </script>

    @stack('scripts')
</body>
</html>