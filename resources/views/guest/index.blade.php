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

        /* navbar – clean glass */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2.5rem;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(229,9,20,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .navbar:hover {
            border-bottom-color: #E50914;
            box-shadow: 0 8px 25px -10px rgba(229,9,20,0.15);
        }

        .logo {
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
        .logo i {
            color: #E50914;
            font-size: 1.9rem;
            transition: transform 0.3s ease;
        }
        .logo:hover i {
            transform: scale(1.05) rotate(3deg);
        }

        .auth-buttons {
            display: flex;
            gap: 0.8rem;
        }
        .nav-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.45rem 1.2rem;
            border-radius: 40px;
            transition: all 0.25s;
            color: #1a1a1f;
            background: transparent;
            border: 1px solid #d0d5dd;
            display: inline-block;
            text-align: center;
        }
        .nav-link:first-child:hover {
            border-color: #E50914;
            background: rgba(229,9,20,0.05);
            color: #E50914;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(229,9,20,0.1);
        }
        .nav-link:last-child {
            background: #E50914;
            border: none;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .nav-link:last-child::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        .nav-link:last-child:hover::after {
            left: 100%;
        }
        .nav-link:last-child:hover {
            background: #b00710;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(229,9,20,0.25);
        }

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
            grid-template-columns: repeat(4, 1fr);
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
        .product-card h3 {
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
            .navbar {
                flex-direction: column;
                gap: 0.8rem;
                padding: 1rem;
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
    </style>
</head>
<body>

<div class="light-follow" id="lightFollow"></div>

<nav class="navbar">
    <a href="#" class="logo">
        <i class="fas fa-bolt"></i>
        <span>ACHILLES</span>
    </a>
    <div class="auth-buttons">
        <a class="nav-link" href="{{ route('login') }}">Login</a>
        <a class="nav-link" href="{{ route('register') }}">Register</a>
    </div>
</nav>

<section class="hero container">
    <div class="hero-content reveal">
        <!-- hero badge text removed as requested -->
        <div class="hero-badge" style="visibility: hidden; display: none;"></div>
        <h1>ACHILLES</h1>
        <div class="tagline">wear your weakness</div>
        <p>Curated collection of authentic performance footwear. Trusted by champions, designed for your everyday greatness. Step into our store and experience the difference.</p>
        <button class="btn-primary" id="discoverBtn">Shop Now <i class="fas fa-arrow-right"></i></button>
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
        <div class="category-card reveal" data-cat="MEN">
            <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600" alt="Men">
            <h3>MEN</h3>
        </div>
        <div class="category-card reveal reveal-delay-1" data-cat="WOMEN">
            <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600" alt="Women">
            <h3>WOMEN</h3>
        </div>
        <div class="category-card reveal reveal-delay-2" data-cat="KIDS">
            <img src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600" alt="Kids">
            <h3>KIDS</h3>
        </div>
        <div class="category-card reveal reveal-delay-3" data-cat="LIMITED">
            <img src="https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600" alt="Limited">
            <h3>LIMITED</h3>
        </div>
    </div>
</div>

<div class="container">
    <h2 class="section-title reveal"><i class="fas fa-fire"></i> Bestsellers</h2>
    <div class="featured-grid">
        <div class="product-card reveal" data-product="Alpha Force">
            <img class="product-img" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400" alt="Alpha Force">
            <h3>Alpha Force</h3>
            <div class="product-price">₱7,490</div>
            <button class="btn-card quick-view">Quick View</button>
        </div>
        <div class="product-card reveal reveal-delay-1" data-product="Solace Run">
            <img class="product-img" src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400" alt="Solace Run">
            <h3>Solace Run</h3>
            <div class="product-price">₱7,990</div>
            <button class="btn-card quick-view">Quick View</button>
        </div>
        <div class="product-card reveal reveal-delay-2" data-product="Trail Master X">
            <img class="product-img" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400" alt="Trail Master X">
            <h3>Trail Master X</h3>
            <div class="product-price">₱8,490</div>
            <button class="btn-card quick-view">Quick View</button>
        </div>
    </div>
</div>

<!-- extra design divider -->
<div class="shape-divider"></div>

<footer>
    <div class="container footer-grid">
        <div class="footer-col">
            <div class="logo" style="margin-bottom: 1rem; justify-content: flex-start;">
                <i class="fas fa-bolt"></i>
                <span>Achilles</span>
            </div>
            <p>Authentic footwear store<br>for the relentless.</p>
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
                <li><a href="#">Authenticity Check</a></li>
                <li><a href="#">Track Order</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>COMPANY</h5>
            <ul>
                <li><a href="#">About Achilles</a></li>
                <li><a href="#">Sustainability</a></li>
                <li><a href="#">Press</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright container">
        <i class="far fa-copyright"></i> 2025 Achilles — Premium Footwear Store.
    </div>
</footer>

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

        // discover button
        document.getElementById('discoverBtn')?.addEventListener('click', () => {
            alert('Welcome to Achilles Footwear Store — explore our authentic collection.');
        });

        // category cards
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', () => {
                const cat = card.querySelector('h3')?.innerText || 'category';
                alert(`Browse ${cat} Please Log In First`);
            });
        });

        // quick view buttons
        document.querySelectorAll('.quick-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const product = btn.closest('.product-card')?.querySelector('h3')?.innerText || 'item';
                alert(`✨ ${product} —Please Log In First to view details and purchase.`);
            });
        });

        // product card click
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if(e.target.classList.contains('quick-view')) return;
                const name = card.querySelector('h3')?.innerText;
                alert(`🛒 ${name} — Please Log In First`);
            });
        });

        // social icons
        document.querySelectorAll('.social-icons i').forEach(icon => {
            icon.addEventListener('click', () => alert('Follow Achilles for exclusive drops and athlete editions.'));
        });

        // footer links
        document.querySelectorAll('.footer-col a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                alert('Full navigation — discover our authentic footwear collections.');
            });
        });
    })();
</script>
</body>
</html>