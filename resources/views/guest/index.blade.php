<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achilles · Wear Your Weakness | Landing Page</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Space Grotesk (futuristic) -->
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
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f0f0f0; }
        ::-webkit-scrollbar-thumb { background: #e53e3e; border-radius: 10px; }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ===== NAVBAR (glassmorphism) ===== */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(229, 62, 62, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            font-size: 1.8rem;
            cursor: pointer;
            text-decoration: none;
            color: #0a0a0f;
        }
        .logo i { color: #e53e3e; font-size: 2rem; }

        .nav-links {
            display: flex;
            gap: 2rem;
            font-weight: 600;
        }
        .nav-link {
            text-decoration: none;
            color: #0a0a0f;
            transition: 0.2s;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid transparent;
        }
        .nav-link:hover {
            border-bottom-color: #e53e3e;
            color: #e53e3e;
        }

        .nav-icons {
            display: flex;
            gap: 1.5rem;
            font-size: 1.25rem;
            align-items: center;
        }
        .nav-icons i {
            color: #0a0a0f;
            cursor: pointer;
            transition: 0.2s;
        }
        .nav-icons i:hover {
            color: #e53e3e;
            transform: scale(1.1);
        }
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        .btn-outline-small {
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            background: transparent;
            border: 1.5px solid #0a0a0f;
            font-weight: 600;
            text-decoration: none;
            color: #0a0a0f;
            transition: 0.2s;
        }
        .btn-outline-small:hover {
            background: #0a0a0f;
            color: white;
        }
        .btn-red-small {
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            background: #e53e3e;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-red-small:hover {
            background: #c53030;
            transform: translateY(-2px);
        }

        /* ===== HERO SECTION ===== */
        .hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 4rem 2rem 5rem;
            gap: 3rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 60%;
            height: 140%;
            background: radial-gradient(circle, rgba(229,62,62,0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-content {
            flex: 1 1 45%;
            z-index: 1;
        }
        .hero-badge {
            display: inline-block;
            background: rgba(229,62,62,0.12);
            backdrop-filter: blur(4px);
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
            letter-spacing: -0.02em;
        }
        .hero-content p {
            font-size: 1.15rem;
            color: #2d2d3a;
            margin: 1.5rem 0;
            max-width: 550px;
        }
        .hero-features {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        .hero-features div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
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
            transition: transform 0.4s;
        }
        .hero-image img:hover {
            transform: scale(1.02) rotate(1deg);
        }

        /* ===== FEATURES GRID ===== */
        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.8rem;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #fafafc, #ffffff);
            border-radius: 80px;
            margin: 2rem auto;
            border: 1px solid rgba(229,62,62,0.1);
        }
        .feature-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 40px;
            background: white;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
        .feature-item i {
            font-size: 2.5rem;
            color: #e53e3e;
            margin-bottom: 0.8rem;
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
            box-shadow: 0 20px 30px -12px rgba(229,62,62,0.2);
        }

        /* ===== CATEGORY GRID (SHOP BY) ===== */
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .section-title i {
            color: #e53e3e;
            font-size: 2.5rem;
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
            transition: 0.4s;
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
        .category-card:hover img {
            transform: scale(1.08);
        }

        /* ===== FEATURED PRODUCTS (preview) ===== */
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
            border: 1px solid #f0f0f0;
            transition: 0.3s;
            text-align: center;
        }
        .product-card:hover {
            transform: translateY(-8px);
            border-color: rgba(229,62,62,0.3);
            box-shadow: 0 20px 30px -12px rgba(229,62,62,0.15);
        }
        .product-img {
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 28px;
            object-fit: cover;
            margin-bottom: 1rem;
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
            transition: 0.2s;
            font-family: inherit;
        }
        .btn-card:hover {
            background: #e53e3e;
            border-color: #e53e3e;
            color: white;
            transform: translateY(-2px);
        }

        /* ===== CTA BANNER ===== */
        .cta-banner {
            background: linear-gradient(135deg, #0a0a0f, #1a1a2a);
            border-radius: 60px;
            margin: 2rem 2rem 4rem;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .cta-banner h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
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
            transition: 0.2s;
        }
        .btn-large:hover {
            background: #c53030;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -8px #e53e3e;
        }

        /* ===== FOOTER ===== */
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
        .footer-col h5 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
        }
        .footer-col ul {
            list-style: none;
        }
        .footer-col li {
            margin-bottom: 0.7rem;
        }
        .footer-col a {
            text-decoration: none;
            color: #555;
            transition: 0.2s;
        }
        .footer-col a:hover {
            color: #e53e3e;
            padding-left: 5px;
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
            color: #0a0a0f;
        }
        .social-icons i:hover {
            color: #e53e3e;
            transform: translateY(-3px);
        }
        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid #e0e4e9;
            color: #777;
        }

        /* Responsive */
        @media (max-width: 1000px) {
            .features, .category-grid, .featured-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .hero-content h1 {
                font-size: 2.8rem;
            }
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
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
            .hero-features {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <a href="#" class="logo">
        <i class="fas fa-shoe-prints"></i>
        <span>Achilles//</span>
    </a>
    
    <div class="nav-icons">
        <i class="fas fa-search"></i>
        <i class="far fa-heart"></i>
        <i class="fas fa-shopping-bag"></i>
        <div class="auth-buttons">
             
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            
             
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero container">
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-bolt"></i> FUTURE DROP 2025</span>
        <h1>RUNNNNNN! <span style="color:#e53e3e;">RED</span><br>LIMITLESS EDITION</h1>
        <p>Experience the next generation of footwear. Carbon-infused soles, adaptive fit, and a design that turns heads. Join the movement.</p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="#" class="btn-red-small" style="padding: 0.9rem 2rem; font-size: 1rem;">SHOP NOW</a>
            <a href="#" class="btn-outline-small" style="padding: 0.9rem 2rem;">EXPLORE COLLECTION</a>
        </div>
        <div class="hero-features">
            <div><i class="fas fa-truck"></i> Free Shipping</div>
            <div><i class="fas fa-rotate-left"></i> 60-Day Returns</div>
            <div><i class="fas fa-gem"></i> Premium Quality</div>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" alt="Red Achilles shoe">
    </div>
</section>

<!-- Features Grid -->
<div class="features container">
    <div class="feature-item">
        <i class="fas fa-crown"></i>
        <h4>Premium Materials</h4>
        <p>Italian leather & breathable mesh</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-cloud-sun"></i>
        <h4>Air-Cooled Comfort</h4>
        <p>360° ventilation system</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-bolt"></i>
        <h4>Energy Return</h4>
        <p>+23% propulsion boost</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-leaf"></i>
        <h4>Eco Rubber Sole</h4>
        <p>Sustainable & durable</p>
    </div>
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
            <img src="https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600" alt="Sale">
            <h3>SALE</h3>
        </a>
    </div>
</div>

<!-- Featured Products Preview -->
<div class="container">
    <h2 class="section-title"><i class="fas fa-fire"></i> Bestsellers</h2>
    <div class="featured-grid">
        <div class="product-card">
            <img class="product-img" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400" alt="Alpha Force">
            <h3>Alpha Force</h3>
            <div class="product-price">₱7,490</div>
            <button class="btn-card">Quick View</button>
        </div>
        <div class="product-card">
            <img class="product-img" src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400" alt="Solace Run">
            <h3>Solace Run</h3>
            <div class="product-price">₱7,990</div>
            <button class="btn-card">Quick View</button>
        </div>
        <div class="product-card">
            <img class="product-img" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400" alt="Trail Master">
            <h3>Trail Master</h3>
            <div class="product-price">₱8,490</div>
            <button class="btn-card">Quick View</button>
        </div>
    </div>
</div>

<!-- Call to Action Banner -->
<div class="cta-banner container">
    <h2>#CAPSTONE FUTURE</h2>
    <p>Join the Achilles community and get 15% off your first order. Early access to limited drops.</p>
    <button class="btn-large" onclick="alert('Welcome! Use code ACHILLES15 at checkout.')">SIGN UP & SAVE →</button>
</div>

<!-- Footer -->
<footer>
    <div class="container footer-grid">
        <div class="footer-col">
            <div class="logo" style="margin-bottom: 1rem;">
                <i class="fas fa-shoe-prints"></i>
                <span>Achilles</span>
            </div>
            <p>White · Black · Red<br>Capstone Project 2025</p>
            <div class="social-icons">
                <i class="fab fa-instagram"></i>
                <i class="fab fa-x-twitter"></i>
                <i class="fab fa-tiktok"></i>
            </div>
        </div>
        
        <div class="footer-col">
            <h5>SUPPORT</h5>
            <ul>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">Shipping Info</a></li>
                <li><a href="#">Returns</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>LEGAL</h5>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright container">
        <i class="far fa-copyright"></i> 2025 Achilles · Built with <i class="fas fa-heart" style="color:#e53e3e;"></i> for capstone project
    </div>
</footer>


</body>
</html>