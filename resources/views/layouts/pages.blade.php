<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Achilles') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/achilles logo foot.png') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
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
        }
        
        /* Navigation */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 2.5rem;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(229,62,62,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            flex-wrap: wrap;
            gap: 0.5rem;
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
            flex-shrink: 0;
        }
        
        .logo i { color: #e53e3e; font-size: 2rem; }
        .site-logo {
            display: block;
            width: auto;
            height: 44px;
            max-width: 200px;
            object-fit: contain;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            font-weight: 600;
            flex-wrap: wrap;
        }
        
        .nav-link {
            text-decoration: none;
            color: #0a0a0f;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid transparent;
            transition: 0.2s;
            font-size: 0.95rem;
        }
        
        .nav-link:hover, .nav-link.active {
            border-bottom-color: #e53e3e;
            color: #e53e3e;
        }
        
        .nav-icons {
            display: flex;
            gap: 0.6rem;
            font-size: 1.25rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        /* ── User Menu ── */
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            padding: 0.45rem 1rem 0.45rem 0.75rem;
            background: #f0f0f0;
            border-radius: 40px;
            transition: 0.2s;
            border: none;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            color: #0a0a0f;
        }
        
        .user-menu:hover {
            background: #e53e3e;
            color: white;
        }
        
        .user-menu i {
            font-size: 1.2rem;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.12);
            min-width: 190px;
            display: none;
            z-index: 100;
            margin-top: 0.5rem;
            padding: 0.4rem 0;
            border: 1px solid #eee;
            overflow: hidden;
        }
        
        .user-menu:hover .user-dropdown,
        .user-dropdown:hover {
            display: block;
        }
        
        .user-dropdown a, .user-dropdown button {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.65rem 1.2rem;
            text-decoration: none;
            color: #0a0a0f;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.15s;
        }
        
        .user-dropdown a:hover, .user-dropdown button:hover {
            background: #f5f5f5;
            color: #e53e3e;
        }
        
        .user-dropdown i {
            width: 1.2rem;
            color: #888;
        }
        .user-dropdown a:hover i, .user-dropdown button:hover i {
            color: #e53e3e;
        }
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 0.2rem 1rem;
        }
        
        /* ── Auth Buttons ── */
        .auth-btn {
            padding: 0.45rem 1.2rem;
            border-radius: 40px;
            background: #f0f0f0;
            color: #0a0a0f;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .auth-btn:hover {
            background: #e53e3e;
            color: white;
            transform: translateY(-2px);
        }
        
        /* ── Search ── */
        .search-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        
        .search-toggle {
            background: transparent;
            border: none;
            font-size: 1.25rem;
            color: #0a0a0f;
            cursor: pointer;
            padding: 0.3rem 0.6rem;
            border-radius: 40px;
            transition: 0.2s;
        }
        
        .search-toggle:hover {
            color: #e53e3e;
            background: #f0f0f0;
        }
        
        .search-input-wrap {
            position: absolute;
            right: 0;
            top: 110%;
            background: #fff;
            border-radius: 40px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.12);
            padding: 0.3rem 0.3rem 0.3rem 1.2rem;
            display: none;
            align-items: center;
            gap: 0.4rem;
            border: 1px solid #eee;
            min-width: 220px;
            z-index: 50;
        }
        .search-input-wrap.open { display: flex; }
        .search-input-wrap input {
            border: none;
            outline: none;
            font-family: inherit;
            font-size: 0.9rem;
            padding: 0.4rem 0;
            background: transparent;
            width: 140px;
        }
        .search-input-wrap button {
            background: #e53e3e;
            border: none;
            color: #fff;
            border-radius: 40px;
            padding: 0.4rem 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .search-input-wrap button:hover {
            background: #c53030;
        }
        
        /* ── Cart Button (unified) ── */
        .cart-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 1.2rem;
            border-radius: 40px;
            background: #0a0a0f;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .cart-btn:hover {
            background: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px -6px rgba(229,62,62,0.4);
        }
        
        .cart-btn .cart-count {
            background: #e53e3e;
            color: #fff;
            padding: 0 0.5rem;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.6;
            min-width: 24px;
            text-align: center;
        }
        .cart-btn:hover .cart-count {
            background: #fff;
            color: #e53e3e;
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
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
        .sale-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #e53e3e;
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 30px;
            font-size: 0.7rem;
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
        
        /* ── Responsive ── */
        @media (max-width: 1100px) {
            .nav-links { gap: 1.2rem; }
            .navbar { padding: 0.75rem 1.5rem; }
        }
        @media (max-width: 900px) {
            .features, .category-grid { grid-template-columns: repeat(2,1fr); }
            .hero-content h1 { font-size: 2.8rem; }
            .nav-links {
                order: 3;
                width: 100%;
                justify-content: center;
                gap: 1rem;
                padding-top: 0.5rem;
                border-top: 1px solid #eee;
            }
            .navbar { padding: 0.75rem 1rem; }
            .search-input-wrap {
                min-width: 180px;
                right: -20px;
            }
            .search-input-wrap input { width: 100px; }
        }
        @media (max-width: 600px) {
            .nav-icons .auth-btn span { display: none; }
            .cart-btn span { display: none; }
            .cart-btn { padding: 0.45rem 0.9rem; }
            .user-menu span { display: none; }
            .user-menu { padding: 0.45rem 0.7rem; }
            .hero-content h1 { font-size: 2.2rem; }
            .features {
                grid-template-columns: 1fr 1fr;
                border-radius: 40px;
                padding: 1.5rem;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- ════════════════════════════════════════ -->
    <!--  NAVIGATION                            -->
    <!-- ════════════════════════════════════════ -->
    <nav class="navbar">
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('images/achilles logo.png') }}" 
                 alt="Achilles Electronics and Computer Shop"
                 class="site-logo">
        </a>
        <div class="nav-links">
            <a href="{{ route('new') }}" class="nav-link {{ request()->routeIs('new') ? 'active' : '' }}">NEW</a>
            <a href="{{ route('men') }}" class="nav-link {{ request()->routeIs('men') ? 'active' : '' }}">MEN</a>
            <a href="{{ route('women') }}" class="nav-link {{ request()->routeIs('women') ? 'active' : '' }}">WOMEN</a>
            <a href="{{ route('kids') }}" class="nav-link {{ request()->routeIs('kids') ? 'active' : '' }}">KIDS</a>
            <a href="{{ route('sale') }}" class="nav-link {{ request()->routeIs('sale') ? 'active' : '' }}">SALE</a>
        </div>
        <div class="nav-icons">
            @auth
                <div class="user-menu" id="userMenu">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ Auth::user()->first_name }}</span>
                    <i class="fas fa-chevron-down" style="font-size:0.7rem; margin-left:4px;"></i>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="{{ route('profile.index') }}">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="{{ route('orders.index') }}">
                            <i class="fas fa-box"></i> My Orders
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button type="submit">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="auth-btn">Login</a>
                <a href="{{ route('register') }}" class="auth-btn">Register</a>
            @endauth

            Search
            <div class="search-wrapper">
                <button class="search-toggle" id="searchToggle" aria-label="Search">
                    <i class="fas fa-search"></i>
                </button>
                <div class="search-input-wrap" id="searchWrap">
                    <input type="text" placeholder="Search products..." id="searchInput" />
                    <button id="searchSubmit"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Unified Cart Button -->
            <a href="{{ route('cart.index') }}" class="cart-btn">
                <i class="fas fa-shopping-bag"></i>
                <span>Cart</span>
                <span class="cart-count" id="cartCount">{{ $cartCount ?? 0 }}</span>
            </a>
        </div>
    </nav>

    <!-- ════════════════════════════════════════ -->
    <!--  MAIN CONTENT                          -->
    <!-- ════════════════════════════════════════ -->
    <main>
        @yield('content')
    </main>

    <!-- ════════════════════════════════════════ -->
    <!--  FOOTER                                -->
    <!-- ════════════════════════════════════════ -->
    <footer>
        <div class="container footer-grid">
            <div class="footer-col">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ asset('images/achilles logo.png') }}" 
                         alt="Achilles Electronics and Computer Shop"
                         class="site-logo">
                </a>
                <div class="social-icons">
                    <i class="fab fa-instagram"></i>
                    <i class="fab fa-x-twitter"></i>
                    <i class="fab fa-tiktok"></i>
                </div>
            </div>
            <div class="footer-col">
                <h5>SHOP</h5>
                <ul>
                    <li><a href="{{ route('new') }}">New</a></li>
                    <li><a href="{{ route('men') }}">Men</a></li>
                    <li><a href="{{ route('women') }}">Women</a></li>
                    <li><a href="{{ route('kids') }}">Kids</a></li>
                    <li><a href="{{ route('sale') }}">Sale</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>SUPPORT</h5>
                <ul>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Size guide</a></li>
                    <li><a href="#">Shipping</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <i class="far fa-copyright"></i> 2026 ACHILLES · built with <i class="fas fa-heart" style="color:#e53e3e;"></i> for capstone
        </div>
    </footer>

    <!-- ════════════════════════════════════════ -->
    <!--  SCRIPTS                               -->
    <!-- ════════════════════════════════════════ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── User Dropdown (click + hover fallback) ──
            const userMenu = document.getElementById('userMenu');
            const userDropdown = document.getElementById('userDropdown');

            if (userMenu && userDropdown) {
                // Click toggle (mobile/tablet)
                userMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isOpen = userDropdown.style.display === 'block';
                    userDropdown.style.display = isOpen ? 'none' : 'block';
                });

                // Click outside closes
                document.addEventListener('click', function() {
                    userDropdown.style.display = 'none';
                });

                // Prevent closing when clicking inside dropdown
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });

                // Hover for desktop (keep as fallback)
                userMenu.addEventListener('mouseenter', function() {
                    if (window.innerWidth > 768) {
                        userDropdown.style.display = 'block';
                    }
                });
                userMenu.addEventListener('mouseleave', function() {
                    if (window.innerWidth > 768) {
                        userDropdown.style.display = 'none';
                    }
                });
            }

            // ── Search Toggle ──
            const toggle = document.getElementById('searchToggle');
            const wrap = document.getElementById('searchWrap');
            const input = document.getElementById('searchInput');
            const submit = document.getElementById('searchSubmit');

            if (toggle && wrap) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    wrap.classList.toggle('open');
                    if (wrap.classList.contains('open')) {
                        input.focus();
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!wrap.contains(e.target) && e.target !== toggle) {
                        wrap.classList.remove('open');
                    }
                });

                const doSearch = function() {
    const query = input.value.trim();
    if (query) {
        window.location.href = '{{ route('search') }}?q=' + encodeURIComponent(query);
    }
};

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') doSearch();
                });
                submit.addEventListener('click', doSearch);

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && wrap.classList.contains('open')) {
                        wrap.classList.remove('open');
                    }
                });
            }

        });
    </script>

    @stack('scripts')
</body>
</html>