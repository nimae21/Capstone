<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achilles · Wear Your Weakness | God‑Tier Landing</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Space Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        /* Futuristic animated grain / mesh */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(229,62,62,0.02) 0%, transparent 60%),
                        radial-gradient(circle at 80% 70%, rgba(0,0,0,0.01) 0%, transparent 70%);
            pointer-events: none;
            z-index: -2;
        }

        ::selection {
            background: #e53e3e;
            color: white;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0f; }
        ::-webkit-scrollbar-thumb { background: #e53e3e; border-radius: 10px; }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ===== NAVBAR (ultra‑glass + neon border) ===== */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2.5rem;
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(229, 62, 62, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .navbar:hover {
            border-bottom-color: #e53e3e;
            box-shadow: 0 10px 30px -10px rgba(229,62,62,0.3);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            font-size: 1.9rem;
            cursor: pointer;
            text-decoration: none;
            color: white;
            text-shadow: 0 0 5px rgba(229,62,62,0.5);
            transition: 0.2s;
        }
        .logo i {
            color: #e53e3e;
            font-size: 2rem;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .logo:hover i {
            transform: scale(1.1) rotate(5deg);
            filter: drop-shadow(0 0 8px #e53e3e);
        }

        .nav-icons {
            display: flex;
            gap: 1.8rem;
            align-items: center;
        }
        .nav-icons i {
            font-size: 1.3rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .nav-icons i:hover {
            color: #e53e3e;
            transform: translateY(-3px) scale(1.1);
            text-shadow: 0 0 8px #e53e3e;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            margin-left: 0.5rem;
        }
        /* Keep original nav-link styling but upgrade */
        .nav-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            transition: all 0.25s;
            color: white;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .nav-link:first-child:hover {
            border-color: #e53e3e;
            background: rgba(229,62,62,0.15);
            color: #e53e3e;
            box-shadow: 0 0 12px rgba(229,62,62,0.4);
            transform: translateY(-2px);
        }
        .nav-link:last-child {
            background: #e53e3e;
            border: none;
        }
        .nav-link:last-child:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(229,62,62,0.5);
        }

        /* ===== HERO SECTION (Wear Your Weakness) ===== */
        .hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 5rem 2rem 6rem;
            gap: 3rem;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 60%;
            height: 140%;
            background: radial-gradient(circle, rgba(229,62,62,0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-content {
            flex: 1 1 45%;
            z-index: 2;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(229,62,62,0.12);
            backdrop-filter: blur(8px);
            color: #e53e3e;
            padding: 0.4rem 1.4rem;
            border-radius: 60px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            border: 1px solid rgba(229,62,62,0.3);
            transition: 0.2s;
        }
        .hero-badge:hover {
            background: rgba(229,62,62,0.2);
            transform: translateX(5px);
        }
        .hero-content h1 {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1.05;
            text-transform: uppercase;
            letter-spacing: -0.02em;
            color: #0a0a0f;
        }
        .hero-gradient-text {
            color: #e53e3e;
            background: linear-gradient(135deg, #e53e3e, #ff6b6b);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }
        .hero-content p {
            font-size: 1.12rem;
            color: #2d2d3a;
            margin: 1.8rem 0 2rem;
            max-width: 560px;
            line-height: 1.6;
            border-left: 3px solid #e53e3e;
            padding-left: 1.2rem;
            transition: 0.2s;
        }
        .hero-content p:hover {
            border-left-color: #ff8c8c;
            transform: translateX(3px);
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-red-small {
            background: #e53e3e;
            border: none;
            padding: 0.9rem 2rem;
            border-radius: 60px;
            font-weight: 700;
            font-family: inherit;
            font-size: 0.95rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            display: inline-block;
            text-decoration: none;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .btn-red-small::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        .btn-red-small:hover::before {
            left: 100%;
        }
        .btn-red-small:hover {
            background: #b91c1c;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -8px #e53e3e;
        }
        .btn-outline-small {
            background: transparent;
            border: 1.5px solid #0a0a0f;
            padding: 0.9rem 2rem;
            border-radius: 60px;
            font-weight: 700;
            font-family: inherit;
            font-size: 0.95rem;
            color: #0a0a0f;
            cursor: pointer;
            transition: 0.25s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-outline-small:hover {
            background: #0a0a0f;
            color: white;
            border-color: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .hero-image {
            flex: 1 1 45%;
            display: flex;
            justify-content: center;
            perspective: 800px;
        }
        .hero-image img {
            width: 100%;
            max-width: 550px;
            border-radius: 48px;
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.4, 1);
            box-shadow: 0 25px 35px -15px rgba(0,0,0,0.2);
            will-change: transform;
        }
        /* 3D tilt + neon glow on hover */
        .hero-image img:hover {
            transform: rotateX(3deg) rotateY(6deg) scale(1.02);
            box-shadow: 0 35px 50px -15px rgba(229,62,62,0.5), 0 0 0 3px rgba(229,62,62,0.3);
            filter: brightness(1.02) contrast(1.05);
        }

        /* ===== FEATURES GRID (futuristic cards) ===== */
        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.8rem;
            padding: 3rem 2rem;
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(2px);
            border-radius: 80px;
            margin: 2rem auto;
            border: 1px solid rgba(229,62,62,0.15);
        }
        .feature-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 40px;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }
        .feature-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 20%, rgba(229,62,62,0.1), transparent);
            opacity: 0;
            transition: 0.3s;
        }
        .feature-item:hover::after {
            opacity: 1;
        }
        .feature-item i {
            font-size: 2.5rem;
            color: #e53e3e;
            margin-bottom: 0.8rem;
            transition: 0.2s;
        }
        .feature-item:hover i {
            transform: scale(1.1);
            text-shadow: 0 0 8px #e53e3e;
        }
        .feature-item h4 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .feature-item p {
            color: #666;
        }
        .feature-item:hover {
            transform: translateY(-8px);
            border-color: rgba(229,62,62,0.4);
            box-shadow: 0 20px 30px -12px rgba(229,62,62,0.25);
        }

        /* ===== CATEGORY GRID (neon border on hover) ===== */
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: linear-gradient(135deg, #0a0a0f, #2d2d3a);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }
        .section-title i {
            color: #e53e3e;
            font-size: 2.5rem;
            filter: drop-shadow(0 0 4px #e53e3e);
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.8rem;
            margin-bottom: 3rem;
        }
        .category-card {
            position: relative;
            border-radius: 40px;
            overflow: hidden;
            aspect-ratio: 1/1;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 15px 25px -10px rgba(0,0,0,0.1);
            text-decoration: none;
            display: block;
        }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .category-card h3 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, #000000dd, transparent);
            color: white;
            padding: 2rem 1.5rem 1.2rem;
            font-size: 1.8rem;
            font-weight: 700;
        }
        .category-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 40px -12px rgba(229,62,62,0.5);
        }
        .category-card:hover img {
            transform: scale(1.08);
        }
        /* glowing border */
        .category-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 40px;
            padding: 2px;
            background: linear-gradient(45deg, #e53e3e, #ff8c8c, transparent);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: 0.3s;
            pointer-events: none;
        }
        .category-card:hover::before {
            opacity: 1;
        }

        /* ===== ENHANCED PRODUCT CARDS (god-tier) ===== */
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .product-card {
            background: white;
            border-radius: 36px;
            padding: 1.5rem;
            border: 1px solid rgba(229,62,62,0.2);
            transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.2);
            text-align: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .product-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #e53e3e, #ff6b6b, #e53e3e);
            border-radius: 38px;
            opacity: 0;
            transition: 0.4s;
            z-index: -1;
        }
        .product-card:hover::before {
            opacity: 0.7;
        }
        .product-card:hover {
            transform: translateY(-12px);
            border-color: transparent;
            box-shadow: 0 25px 40px -12px rgba(229,62,62,0.5);
        }
        .product-img {
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 28px;
            object-fit: cover;
            margin-bottom: 1rem;
            transition: 0.3s;
        }
        .product-card:hover .product-img {
            transform: scale(1.03);
            filter: brightness(1.05);
        }
        .product-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
        }
        .product-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #e53e3e;
            margin: 0.5rem 0;
        }
        .btn-card {
            background: transparent;
            border: 1.5px solid #e0e0e0;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .product-card:hover .btn-card {
            background: #e53e3e;
            border-color: #e53e3e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(229,62,62,0.4);
        }

        /* ===== CTA BANNER (neon pulse) ===== */
        .cta-banner {
            background: linear-gradient(135deg, #0a0a0f, #1a1a2e);
            border-radius: 60px;
            margin: 2rem 2rem 4rem;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(229,62,62,0.3);
            transition: 0.3s;
        }
        .cta-banner:hover {
            border-color: #e53e3e;
            box-shadow: 0 0 35px rgba(229,62,62,0.2);
        }
        .cta-banner h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 0 0 5px #e53e3e;
        }
        .cta-banner p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            background: #e53e3e;
            color: white;
            border: none;
            border-radius: 60px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 0 12px #e53e3e;
        }
        .btn-large:hover {
            background: #b91c1c;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -8px #e53e3e;
        }

        /* ===== FOOTER (futuristic dark) ===== */
        footer {
            background: rgba(10,10,15,0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(229,62,62,0.2);
            padding: 3rem 2rem 2rem;
            margin-top: 2rem;
            color: #eee;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2.5rem;
        }
        .footer-col h5 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            color: #e53e3e;
        }
        .footer-col ul {
            list-style: none;
        }
        .footer-col li {
            margin-bottom: 0.7rem;
        }
        .footer-col a {
            text-decoration: none;
            color: #ccc;
            transition: 0.2s;
        }
        .footer-col a:hover {
            color: #e53e3e;
            padding-left: 5px;
            text-shadow: 0 0 3px #e53e3e;
        }
        .social-icons {
            display: flex;
            gap: 1rem;
            font-size: 1.5rem;
            margin-top: 1rem;
        }
        .social-icons i {
            cursor: pointer;
            transition: 0.2s;
            color: #ccc;
        }
        .social-icons i:hover {
            color: #e53e3e;
            transform: translateY(-4px) scale(1.1);
            filter: drop-shadow(0 0 6px #e53e3e);
        }
        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #aaa;
        }

        /* Responsive */
        @media (max-width: 1000px) {
            .features, .category-grid, .featured-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .hero-content h1 {
                font-size: 3rem;
            }
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
        }
        @media (max-width: 700px) {
            .features, .category-grid, .featured-grid {
                grid-template-columns: 1fr;
            }
            .hero {
                flex-direction: column;
                text-align: center;
            }
            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Navigation (original Laravel routes preserved) -->
<nav class="navbar">
    <a href="#" class="logo">
        <i class="fas fa-bolt"></i>
        <span>ACHILLES</span>
    </a>
    <div class="nav-icons">
        <i class="fas fa-search"></i>
        <i class="far fa-heart"></i>
        <i class="fas fa-shopping-bag"></i>
        <div class="auth-buttons">
            <!-- Original Blade routes – fully functional -->
            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
        </div>
    </div>
</nav>

<!-- Hero Section (updated: Wear Your Weakness + new quote, removed shipping/returns) -->
<section class="hero container">
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-shield-alt"></i> LEGACY EDITION // WEAKNESS IS ARMOR</span>
        <h1>ACHILLES <span class="hero-gradient-text">WEAR YOUR</span><br>WEAKNESS</h1>
        <p>“Every weakness has a weight — we turn it into momentum. Designed to carry your scars, absorb your doubts, and push you forward with every step. This isn’t just footwear. This is how you rise.”</p>
        <div class="hero-buttons">
            <a href="#" class="btn-red-small">UNLEASH YOUR EDGE →</a>
            <a href="#" class="btn-outline-small">THE PHILOSOPHY</a>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" alt="Achilles Red Edition">
    </div>
</section>

<!-- Features Grid (enhanced) -->
<div class="features container">
    <div class="feature-item"><i class="fas fa-microchip"></i><h4>Neural Mesh</h4><p>Adaptive breathable weave</p></div>
    <div class="feature-item"><i class="fas fa-charging-station"></i><h4>Energy Arc</h4><p>+27% responsive return</p></div>
    <div class="feature-item"><i class="fas fa-cloud-moon"></i><h4>Zero Gravity Foam</h4><p>Featherlight cushion</p></div>
    <div class="feature-item"><i class="fas fa-recycle"></i><h4>Evo Rubber</h4><p>Eco-traction system</p></div>
</div>

<!-- Shop by Category -->
<div class="container">
    <h2 class="section-title"><i class="fas fa-compass"></i> Shop by Category</h2>
    <div class="category-grid">
        <a href="#" class="category-card">
            <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600" alt="Men">
            <h3>MEN</h3>
        </a>
        <a href="#" class="category-card">
            <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600" alt="Women">
            <h3>WOMEN</h3>
        </a>
        <a href="#" class="category-card">
            <img src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600" alt="Kids">
            <h3>KIDS</h3>
        </a>
        <a href="#" class="category-card">
            <img src="https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600" alt="Limited">
            <h3>LIMITED</h3>
        </a>
    </div>
</div>

<!-- Featured Products -->
<div class="container">
    <h2 class="section-title"><i class="fas fa-fire"></i> Bestsellers // Energy Pack</h2>
    <div class="featured-grid">
        <div class="product-card">
            <img class="product-img" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400" alt="Alpha Force">
            <h3>Alpha Force</h3>
            <div class="product-price">₱7,490</div>
            <button class="btn-card quick-view">Quick View</button>
        </div>
        <div class="product-card">
            <img class="product-img" src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400" alt="Solace Run">
            <h3>Solace Run</h3>
            <div class="product-price">₱7,990</div>
            <button class="btn-card quick-view">Quick View</button>
        </div>
        <div class="product-card">
            <img class="product-img" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400" alt="Trail Master">
            <h3>Trail Master X</h3>
            <div class="product-price">₱8,490</div>
            <button class="btn-card quick-view">Quick View</button>
        </div>
    </div>
</div>

<!-- CTA Banner -->
<div class="cta-banner container">
    <h2>#WEARYOURWEAKNESS</h2>
    <p>Join the movement. First access to Achilles RED CELL & exclusive members-only gear.</p>
    <button class="btn-large" id="ctaBtn">CLAIM 15% OFF →</button>
</div>

<!-- Footer -->
<footer>
    <div class="container footer-grid">
        <div class="footer-col">
            <div class="logo" style="margin-bottom: 1rem; justify-content: flex-start;">
                <i class="fas fa-bolt"></i>
                <span>Achilles</span>
            </div>
            <p>Where weakness becomes armor.<br>Capstone 2025</p>
            <div class="social-icons">
                <i class="fab fa-instagram"></i>
                <i class="fab fa-x-twitter"></i>
                <i class="fab fa-tiktok"></i>
            </div>
        </div>
        <div class="footer-col">
            <h5>EXPLORE</h5>
            <ul>
                <li><a href="#">New Arrivals</a></li>
                <li><a href="#">Men's</a></li>
                <li><a href="#">Women's</a></li>
                <li><a href="#">Accessories</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>SUPPORT</h5>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">Returns</a></li>
                <li><a href="#">Track Order</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>LEGACY</h5>
            <ul>
                <li><a href="#">About Achilles</a></li>
                <li><a href="#">Sustainability</a></li>
                <li><a href="#">Press</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright container">
        <i class="far fa-copyright"></i> 2025 Achilles — Wear Your Weakness. Forged in vulnerability.
    </div>
</footer>

<!-- Simple interactive demo for non‑auth buttons (keeps original login/register untouched) -->
<script>
    (function() {
        // Hero buttons
        const shopBtn = document.querySelector('.btn-red-small');
        const exploreBtn = document.querySelector('.btn-outline-small');
        if(shopBtn) shopBtn.addEventListener('click', (e) => { e.preventDefault(); alert('⚡ EDGE UNLOCKED — RED CELL collection awaits.'); });
        if(exploreBtn) exploreBtn.addEventListener('click', (e) => { e.preventDefault(); alert('“Every weakness has a weight — we turn it into momentum.” Read the manifesto.'); });
        
        // CTA button
        const cta = document.getElementById('ctaBtn');
        if(cta) cta.addEventListener('click', () => alert('🏹 Join Achilles Club: use code WEAKNESS15 for 15% off.'));
        
        // Category cards
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                const cat = card.querySelector('h3')?.innerText || 'collection';
                alert(`⚡ Explore ${cat} — gear built to turn limits into legends.`);
            });
        });
        
        // Quick view buttons
        document.querySelectorAll('.quick-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const product = btn.closest('.product-card')?.querySelector('h3')?.innerText || 'item';
                alert(`✨ ${product} — engineered for those who dare. Add to wishlist.`);
            });
        });
        
        // Product cards (except quick view)
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if(e.target.classList.contains('quick-view')) return;
                const name = card.querySelector('h3')?.innerText;
                alert(`🛒 ${name} — Wear your weakness, own your power.`);
            });
        });
        
        // Social icons demo
        document.querySelectorAll('.social-icons i').forEach(icon => {
            icon.addEventListener('click', () => alert('🌐 Follow Achilles for exclusive drops.'));
        });
        
        // Footer links
        document.querySelectorAll('.footer-col a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                alert('🚀 Full navigation ready in production. Stay tuned!');
            });
        });
    })();
</script>
</body>
</html>