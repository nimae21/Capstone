<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>STRIDE · @yield('title', 'Futuristic Footwear')</title>
    
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
            padding: 1rem 2.5rem;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(229,62,62,0.2);
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
            gap: 2.5rem;
            font-weight: 600;
        }
        
        .nav-link {
            text-decoration: none;
            color: #0a0a0f;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid transparent;
            transition: 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            border-bottom-color: #e53e3e;
            color: #e53e3e;
        }
        
        .nav-icons {
            display: flex;
            gap: 1.5rem;
            font-size: 1.25rem;
            align-items: center;
        }
        
        .nav-icons i, .auth-btn {
            color: #0a0a0f;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }
        
        .nav-icons i:hover, .auth-btn:hover {
            color: #e53e3e;
            transform: scale(1.1);
        }
        
        .auth-btn {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            background: #f0f0f0;
        }
        
        .auth-btn:hover {
            background: #e53e3e;
            color: white;
            transform: translateY(-2px);
        }
        
       
     
        
        /* Buttons */
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
        
        .btn-card {
            background: transparent;
            border: 1.5px solid #e0e0e0;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            border-radius: 40px;
            cursor: pointer;
            transition: 0.2s;
        }
        
        .btn-card:hover {
            background: #e53e3e;
            border-color: #e53e3e;
            color: white;
        }
        
        /* Container */
        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Hero Section */
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
        
        /* Features Grid */
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
        
        /* Category Grid */
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
        
        /* Category Pages */
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
        
        /* CTA Banner */
        .cta-banner {
            background: linear-gradient(135deg, #0a0a0f, #1a1a2a);
            border-radius: 60px;
            margin: 2rem 2rem 4rem;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
        }
        
        /* Footer */
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


        .user-menu {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem 1rem;
    background: #f0f0f0;
    border-radius: 40px;
    transition: 0.2s;
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
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    min-width: 180px;
    display: none;
    z-index: 100;
    margin-top: 0.5rem;
}

.user-menu:hover .user-dropdown {
    display: block;
}

.user-dropdown a, .user-dropdown button {
    display: block;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #0a0a0f;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
}

.user-dropdown a:hover, .user-dropdown button:hover {
    background: #f5f5f5;
    color: #e53e3e;
}

.auth-btn {
    padding: 0.5rem 1.2rem;
    border-radius: 40px;
    background: #f0f0f0;
    color: #0a0a0f;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s;
}

.auth-btn:hover {
    background: #e53e3e;
    color: white;
    transform: translateY(-2px);
}
        
        .social-icons i { cursor: pointer; transition: 0.2s; }
        .social-icons i:hover { color: #e53e3e; transform: translateY(-3px); }
        
        @media (max-width: 900px) {
            .features, .category-grid { grid-template-columns: repeat(2,1fr); }
            .hero-content h1 { font-size: 2.8rem; }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('home') }}" class="logo">
            <i class="fas fa-shoe-prints"></i>
            <span>Achilles</span>
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
        <div class="user-menu">
            <i class="fas fa-user-circle"></i>
            <span>{{ Auth::user()->first_name }}</span>
            <div class="user-dropdown">
                <a href="#">My Profile</a>
                <a href="#">My Orders</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="auth-btn">Login</a>
        <a href="{{ route('register') }}" class="auth-btn">Register</a>
    @endauth
    <i class="fas fa-search"></i>
    <i class="far fa-heart"></i>
    <div class="cart-icon" onclick="toggleCart()">
        <i class="fas fa-shopping-bag"></i>
        <span id="cartCount" class="cart-count">0</span>
    </div>
</div>
    </nav>

   

    

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container footer-grid">
            <div class="footer-col">
                <a href="{{ route('home') }}" class="logo" style="display: inline-flex;">
                    <i class="fas fa-shoe-prints"></i>
                    <span>STRIDE</span>
                </a>
                <p>White · Black · Red<br>Capstone Project 2025</p>
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
        <div class="copyright" style="text-align:center; padding-top:2rem;">
            <i class="far fa-copyright"></i> 2025 STRIDE · built with <i class="fas fa-heart" style="color:#e53e3e;"></i> for capstone
        </div>
    </footer>

    <script>
        
        
        
    </script>
    
    @stack('scripts')
</body>
</html>