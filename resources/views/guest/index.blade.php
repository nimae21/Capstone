<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Achilles · Premium Footwear Store | Trusted by Champions</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter + Space Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        footer .logo {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            font-size: 1.8rem;
            cursor: pointer;
            text-decoration: none;
            color: #1a1a1f;
            letter-spacing: -0.02em;
            font-family: 'Space Grotesk', monospace;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #1a1a1f;
            line-height: 1.5;
            scroll-behavior: smooth;
            overflow-x: hidden;
            cursor: auto;
        }

        ::selection {
            background: #E50914;
            color: white;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f0f0f0;
        }
        ::-webkit-scrollbar-thumb {
            background: #E50914;
            border-radius: 10px;
        }

        /* animated gradient mesh */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(229,9,20,0.02) 0%, transparent 60%),
                        radial-gradient(circle at 80% 70%, rgba(0,0,0,0.01) 0%, transparent 70%);
            pointer-events: none;
            z-index: -1;
        }

        /* cinematic light follow */
        .light-follow {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(229,9,20,0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            transform: translate(-50%, -50%);
            transition: transform 0.08s ease-out;
            z-index: -1;
            opacity: 0.7;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* reveal animations */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1), transform 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }

        /* hero section */
        .hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            min-height: 85vh;
            padding: 4rem 2rem 6rem;
            gap: 3rem;
            background: #ffffff;
            position: relative;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #E50914, transparent);
            opacity: 0.4;
        }
        .hero-content {
            flex: 1 1 45%;
            z-index: 2;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(229,9,20,0.08);
            color: #E50914;
            padding: 0.4rem 1.4rem;
            border-radius: 60px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid rgba(229,9,20,0.25);
            backdrop-filter: blur(4px);
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(229,9,20,0.2); }
            70% { box-shadow: 0 0 0 6px rgba(229,9,20,0); }
            100% { box-shadow: 0 0 0 0 rgba(229,9,20,0); }
        }
        .hero-content h1 {
            font-size: 4.8rem;
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #1a1a1f 0%, #E50914 80%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }
        .tagline {
            font-size: 1.8rem;
            font-weight: 500;
            color: #E50914;
            letter-spacing: -0.01em;
            border-left: 4px solid #E50914;
            padding-left: 1rem;
            margin: 1rem 0 1.5rem;
        }
        .hero-content p {
            font-size: 1rem;
            color: #4a4a55;
            max-width: 500px;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background: #E50914;
            border: none;
            padding: 0.9rem 2.2rem;
            border-radius: 60px;
            font-weight: 700;
            font-family: inherit;
            font-size: 0.95rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 4px 15px rgba(229,9,20,0.25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        .btn-primary:hover::before {
            left: 100%;
        }
        .btn-primary:hover {
            background: #b00710;
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(229,9,20,0.35);
        }
        .hero-image {
            flex: 1 1 45%;
            display: flex;
            justify-content: center;
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }
        .hero-image img {
            width: 100%;
            max-width: 520px;
            border-radius: 48px;
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.4, 1);
            box-shadow: 0 25px 40px -12px rgba(0,0,0,0.15);
        }
        .hero-image img:hover {
            transform: scale(1.02) rotate(1deg);
            box-shadow: 0 35px 50px -15px rgba(229,9,20,0.25);
        }

        /* looping banner – names only */
        .loop-banner {
            background: #f8f8fc;
            border-top: 1px solid #eef2f5;
            border-bottom: 1px solid #eef2f5;
            padding: 1rem 0;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
        }
        .banner-track {
            display: inline-block;
            animation: scrollBanner 30s linear infinite;
            font-weight: 600;
            font-size: 1rem;
            color: #1a1a1f;
            letter-spacing: 0.2px;
        }
        .banner-track span {
            margin: 0 1.5rem;
        }
        .banner-track i {
            color: #E50914;
            margin-right: 8px;
        }
        @keyframes scrollBanner {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* category grid */
        .section-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 3.5rem 0 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #1a1a1f;
        }
        .section-title i {
            color: #E50914;
            font-size: 2rem;
            filter: drop-shadow(0 0 4px rgba(229,9,20,0.3));
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.8rem;
            margin-bottom: 4rem;
        }
        .category-card {
            position: relative;
            border-radius: 32px;
            overflow: hidden;
            aspect-ratio: 1/1;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 12px 25px -10px rgba(0,0,0,0.08);
            transform-style: preserve-3d;
        }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .category-card h3 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 1.6rem 1.2rem 1rem;
            font-size: 1.5rem;
            font-weight: 800;
        }
        .category-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(229,9,20,0.2), transparent);
            opacity: 0;
            transition: 0.3s;
        }
        .category-card:hover::after {
            opacity: 1;
        }
        .category-card:hover {
            transform: translateY(-12px) rotateX(3deg);
            box-shadow: 0 28px 35px -15px rgba(229,9,20,0.35);
        }
        .category-card:hover img {
            transform: scale(1.08);
        }

        /* product cards */
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 5rem;
        }
        .product-card {
            background: #ffffff;
            border-radius: 32px;
            padding: 1.5rem;
            border: 1px solid #edeef2;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.2);
            text-align: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 14px rgba(0,0,0,0.02);
            transform-style: preserve-3d;
        }
        .product-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(229,9,20,0.08), transparent);
            opacity: 0;
            transition: 0.4s;
        }
        .product-card:hover::before {
            opacity: 1;
        }
        .product-card:hover {
            transform: translateY(-12px) rotateX(2deg);
            border-color: #E50914;
            box-shadow: 0 30px 40px -18px rgba(229,9,20,0.3);
        }
        .product-img {
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 28px;
            object-fit: cover;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-img {
            transform: scale(1.03);
        }
        .product-card .product-name {
            display: block;
            font-size: 1.35rem;
            font-weight: 800;
            color: #1a1a1f;
        }
        .product-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #E50914;
            margin: 0.5rem 0;
        }
        .btn-card {
            background: transparent;
            border: 1.5px solid #e2e6ea;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.25s;
            color: #1a1a1f;
        }
        .product-card:hover .btn-card {
            background: #E50914;
            border-color: #E50914;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(229,9,20,0.2);
        }

        /* extra design: subtle shape divider */
        .shape-divider {
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, #E50914, #E50914, transparent);
            margin: 1rem 0;
        }

        /* Footer */
        footer {
            background: #fafafc;
            border-top: 1px solid #eef2f5;
            padding: 3rem 2rem 2rem;
            margin-top: 2rem;
            color: #4a4a55;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 2.5rem;
        }
        .footer-col h5 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            color: #E50914;
            letter-spacing: 0.5px;
        }
        .footer-col ul {
            list-style: none;
        }
        .footer-col li {
            margin-bottom: 0.7rem;
        }
        .footer-col a {
            text-decoration: none;
            color: #6c6c78;
            transition: 0.2s;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .footer-col a:hover {
            color: #E50914;
            padding-left: 5px;
        }
        .social-icons {
            display: flex;
            gap: 1rem;
            font-size: 1.4rem;
            margin-top: 1rem;
        }
        .social-icons i {
            cursor: pointer;
            transition: 0.2s;
            color: #6c6c78;
        }
        .social-icons i:hover {
            color: #E50914;
            transform: translateY(-4px) scale(1.1);
        }
        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid #e2e6ea;
            color: #8a8a95;
            font-size: 0.8rem;
        }

        @media (max-width: 1000px) {
            .category-grid, .featured-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .hero-content h1 {
                font-size: 3.2rem;
            }
            .tagline {
                font-size: 1.4rem;
            }
        }
        @media (max-width: 700px) {
            .category-grid, .featured-grid {
                grid-template-columns: 1fr;
            }
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 3rem 1rem;
                min-height: auto;
            }
            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }
            .tagline {
                border-left: none;
                padding-left: 0;
                text-align: center;
            }
            .container {
                padding: 0 1.2rem;
            }
            
        }
        button { font-family: inherit; }
        button.btn-primary { border: 0; cursor: pointer; }
        button.product-card { font: inherit; color: inherit; text-align: center; width: 100%; }
        .product-placeholder { display: grid; place-items: center; background: #f4f4f6; font-size: 3rem; color: #858590; }
        .empty-bestsellers { grid-column: 1 / -1; padding: 2rem; text-align: center; color: #6c6c78; }
        .social-button { border: 0; background: transparent; font-size: inherit; cursor: pointer; }
        :focus-visible { outline: 3px solid #E50914; outline-offset: 5px; }
        body.modal-open { overflow: hidden; }
        .landing-modal { margin: auto; width: min(520px, calc(100% - 2rem)); max-height: calc(100dvh - 2rem); overflow-y: auto; border: 0; border-radius: 24px; padding: 2.5rem 2rem 2rem; color: #1a1a1f; box-shadow: 0 24px 80px #0003; }
        .landing-modal::backdrop { background: rgba(15, 15, 20, .6); backdrop-filter: blur(4px); }
        .modal-close { position: absolute; right: 1rem; top: .65rem; background: transparent; border: 0; font-size: 1.8rem; cursor: pointer; color: #6c6c78; }
        .modal-icon { color: #E50914; font-size: 2rem; margin-bottom: 1rem; }
        .landing-modal h2 { font-family: 'Space Grotesk', sans-serif; margin-bottom: .8rem; font-size: 1.7rem; }
        .landing-modal p { color: #6c6c78; margin-bottom: 1rem; }
        .modal-actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; }
        .modal-actions a { flex: 1; padding: .8rem 1rem; }
        .draft-label { display: inline-block; background: #f5f5f7; color: #6c6c78; border-radius: 6px; padding: .2rem .6rem; font-size: .75rem; margin-bottom: 1rem; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/responsive.css') . '?v=' . filemtime(public_path('css/responsive.css')) }}">
    <script src="{{ asset('js/responsive.js') . '?v=' . filemtime(public_path('js/responsive.js')) }}" defer></script>
    @include('partials.customer-header-assets')
</head>
<body class="store-site">

<div class="light-follow" id="lightFollow"></div>

@include('partials.customer-header')

<section class="hero container">
    <div class="hero-content reveal">
        <!-- hero badge text removed as requested -->
        <div class="hero-badge" style="visibility: hidden; display: none;"></div>
        <h1>ACHILLES</h1>
        <div class="tagline">wear your weakness</div>
        <p>Curated collection of authentic performance footwear. Trusted by champions, designed for your everyday greatness. Step into our store and experience the difference.</p>
        <button type="button" data-auth class="btn-primary">Shop Now <i class="fas fa-arrow-right"></i></button>
    </div>
    <div class="hero-image reveal reveal-delay-1">
        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" alt="Achilles signature shoe">
    </div>
</section>

<!-- Looping banner – list of names: famous who trust Achilles -->
<div class="loop-banner">
    <div class="banner-track">
        <span></i> FAMOUS WHO TRUST ACHILLES ></span>
        <span>June Mar Fajardo</span>
        <span>Vic Manuel</span>
        <span>Tony Mitchell</span>
        <span>other MPBL Pros</span>
        <span>Eri Neeman</span>
        <span>Marwen Jay Nazar</span>
        <span></i> FAMOUS WHO TRUST ACHILLES > </span>
        <span>June Mar Fajardo</span>
        <span>Vic Manuel</span>
        <span>Tony Mitchell</span>
        <span>other MPBL Pros</span>
        <span>Eri Neeman</span>
        <span>Marwen Jay Nazar</span>
        <span></i> FAMOUS WHO TRUST ACHILLES > </span>
        <span>June Mar Fajardo</span>
        <span>Vic Manuel</span>
        <span>Tony Mitchell</span>
        <span>other MPBL Pros</span>
        <span>Eri Neeman</span>
        <span>Marwen Jay Nazar</span>
    </div>
</div>

<div class="container">
    <h2 class="section-title reveal"><i class="fas fa-compass"></i> Shop by Category</h2>
    <div class="category-grid">
        <div class="category-card reveal" data-auth role="button" tabindex="0" aria-label="Shop men">
            <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600" alt="Men">
            <h3>MEN</h3>
        </div>
        <div class="category-card reveal reveal-delay-1" data-auth role="button" tabindex="0" aria-label="Shop women">
            <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600" alt="Women">
            <h3>WOMEN</h3>
        </div>
        <div class="category-card reveal reveal-delay-2" data-auth role="button" tabindex="0" aria-label="Shop kids">
            <img src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600" alt="Kids">
            <h3>KIDS</h3>
        </div>
    </div>
</div>

<div class="container">
    <h2 class="section-title reveal"><i class="fas fa-fire"></i> Bestsellers</h2>
    <div class="featured-grid">
        @forelse ($products as $product)
            @php($image = $product->primaryImage ?? $product->images->first())
            <button type="button" class="product-card reveal" data-auth aria-label="View {{ $product->product_name }}">
                @if ($image)
                    <img class="product-img" src="{{ $image->image_url }}" alt="{{ $product->product_name }}" loading="lazy">
                @else
                    <span class="product-img product-placeholder"><i class="fas fa-shoe-prints" aria-hidden="true"></i></span>
                @endif
                @include('partials.new-arrival-badge')
                <span class="product-name">{{ $product->product_name }}</span>
                <span class="product-price" style="display: block;">
                    @if ($product->display_price !== null)
                        From &#8369;{{ number_format($product->display_price, 2) }}
                    @else
                        Price coming soon
                    @endif
                </span>
                <span class="btn-card">View Shoe</span>
            </button>
        @empty
            <p class="empty-bestsellers">Our best sellers are on their way. Check back soon to discover our customers' favorites.</p>
        @endforelse
    </div>
</div>

<!-- extra design divider -->
<div class="shape-divider"></div>

<footer>
    <div class="container footer-grid">
        <div class="footer-col">
            <div class="logo" style="margin-bottom: 1rem; justify-content: flex-start;">
    <img src="{{ asset('images/achilles logo.png') }}" alt="Achilles Logo" class="logo-image">
    
</div>
            <p>Authentic footwear store<br>for the relentless.</p>
            <div class="social-icons">
                <button type="button" class="social-button" data-info="facebook" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></button>
                <button type="button" class="social-button" data-info="x-twitter" aria-label="X"><i class="fab fa-x-twitter" aria-hidden="true"></i></button>
                <button type="button" class="social-button" data-info="tiktok" aria-label="TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="footer-col">
            <h5>EXPLORE</h5>
            <ul>
                <li><a href="{{ route('login') }}" data-auth>Men's</a></li>
                <li><a href="{{ route('login') }}" data-auth>Women's</a></li>
                <li><a href="{{ route('login') }}" data-auth>Kids</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>SUPPORT</h5>
            <ul>
                <li><a href="#info-modal" data-info="help">Help Center</a></li>
                <li><a href="#info-modal" data-info="size">Size Guide</a></li>
                <li><a href="#info-modal" data-info="authenticity">Authenticity Check</a></li>
                <li><a href="#info-modal" data-info="tracking">Track Order</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>COMPANY</h5>
            <ul>
                <li><a href="#info-modal" data-info="about">About Achilles</a></li>
                <li><a href="#info-modal" data-info="sustainability">Sustainability</a></li>
                <li><a href="#info-modal" data-info="press">Press</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright container">
        <i class="far fa-copyright"></i> 2025 Achilles — Premium Footwear Store.
    </div>
</footer>

<dialog id="auth-modal" class="landing-modal" aria-labelledby="auth-title" aria-describedby="auth-description">
    <button type="button" class="modal-close" data-close aria-label="Close">&times;</button>
    <div class="modal-icon"><i class="fas fa-shoe-prints" aria-hidden="true"></i></div>
    <h2 id="auth-title">Your next pair awaits</h2>
    <p id="auth-description">Log in or create an account to explore our shoes, find your size, and shop with Achilles.</p>
    <div class="modal-actions auth-buttons">
        <a class="nav-link" href="{{ route('login') }}">Log In</a>
        <a class="nav-link" href="{{ route('register') }}">Register</a>
    </div>
</dialog>
<dialog id="info-modal" class="landing-modal" aria-labelledby="info-title">
    <button type="button" class="modal-close" data-close aria-label="Close">&times;</button>
    <span class="draft-label">Draft information</span>
    <h2 id="info-title"></h2>
    <div id="info-content"></div>
</dialog>
<script>
    (function() {
        // cinematic mouse light
        const light = document.getElementById('lightFollow');
        if (light) {
            document.addEventListener('mousemove', (e) => {
                light.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
            });
        }

        // scroll reveal
        const reveals = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        reveals.forEach(el => observer.observe(el));

        // Editable placeholder copy for footer information dialogs.
        const information = {
            help: ['Help Center', 'After logging in, browse a category, choose a shoe, and check the available sizes before adding it to your cart.', 'For order questions, keep your order number ready. Store contact details and support hours will be added here.'],
            size: ['Size Guide', 'Measure each foot from heel to longest toe while standing and use the larger measurement.', 'Sizing varies by brand and model. Compare your measurement with the brand size chart before ordering. A detailed chart will be added here.'],
            authenticity: ['Authenticity Check', 'Review the product details, labels, stitching, and packaging. Keep your receipt and original packaging for follow-up questions.', 'Prepare your order number and clear photos when asking about a product. Our verification process and contact details will be added here.'],
            tracking: ['Track Order', 'Log in to the account used for your purchase and open My Orders to review your order status.', 'Courier tracking guidance will be added here. This popup does not look up an order.'],
            about: ['About Achilles', 'Achilles is a family-owned footwear store built around a love of shoes and helping customers find their next pair.', 'Our store story, location, and opening hours will be added here.'],
            sustainability: ['Sustainability', 'Help your shoes last longer: clean them according to their material, air-dry them, and store them in a cool, dry place.', 'Details about store packaging and any sustainability initiatives will be added here.'],
            press: ['Press', 'This space will feature Achilles news, store announcements, and media resources.', 'A media contact and approved brand assets will be added here for press and collaboration inquiries.'],
            facebook: ['Find us on Facebook', 'Our official Facebook page link will be added here. Follow Achilles for store news and footwear updates.'],
            'x-twitter': ['Find us on X', 'Our official X profile link will be added here.'],
            tiktok: ['Find us on TikTok', 'Our official TikTok profile link will be added here. Follow Achilles for shoe videos and store updates.'],
        };
        const authModal = document.getElementById('auth-modal');
        const infoModal = document.getElementById('info-modal');
        function openModal(modal) {
            modal.showModal();
            document.body.classList.add('modal-open');
        }
        document.querySelectorAll('[data-auth]').forEach(trigger => {
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.addEventListener('click', event => {
                event.preventDefault();
                openModal(authModal);
            });
            if (trigger.getAttribute('role') === 'button') {
                trigger.addEventListener('keydown', event => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        trigger.click();
                    }
                });
            }
        });
        document.querySelectorAll('[data-info]').forEach(trigger => {
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.addEventListener('click', event => {
                event.preventDefault();
                const [title, ...paragraphs] = information[trigger.dataset.info];
                document.getElementById('info-title').textContent = title;
                document.getElementById('info-content').replaceChildren(...paragraphs.map(text => {
                    const paragraph = document.createElement('p');
                    paragraph.textContent = text;
                    return paragraph;
                }));
                openModal(infoModal);
            });
        });
        document.querySelectorAll('.landing-modal').forEach(modal => {
            modal.querySelector('[data-close]').addEventListener('click', () => modal.close());
            modal.addEventListener('click', event => {
                const bounds = modal.getBoundingClientRect();
                if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) modal.close();
            });
            modal.addEventListener('close', () => document.body.classList.remove('modal-open'));
        });
    })();
</script>
</body>
</html>