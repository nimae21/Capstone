@extends('layouts.pages')

@section('title', 'Home - Futuristic Footwear')

@section('content')
<div class="container hero">
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-bolt"></i> FUTURE DROP 2025</span>
        <h1>RUN IN <span style="color:#e53e3e;">RED</span><br>PREMIUM COLLECTION</h1>
        <p>Limitless performance meets cybernetic design. Ultra-responsive cushioning, premium materials, and free shipping worldwide.</p>
        <div style="display:flex; gap:1rem;">
            <a href="{{ route('men') }}" class="btn btn-red">SHOP MEN</a>
            <a href="{{ route('women') }}" class="btn btn-outline">SHOP WOMEN</a>
        </div>
        <div class="hero-features">
            <div><i class="fas fa-truck"></i> Free shipping</div>
            <div><i class="fas fa-rotate-left"></i> 60-day returns</div>
            <div><i class="fas fa-gem"></i> Premium quality</div>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" alt="red shoe">
    </div>
</div>

<div class="features container">
    <div class="feature-item">
        <i class="fas fa-crown"></i>
        <h4>PREMIUM</h4>
        <p>Italian leather & mesh</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-cloud-sun"></i>
        <h4>AIR-COOLED</h4>
        <p>360° ventilation system</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-bolt"></i>
        <h4>ENERGY RETURN</h4>
        <p>+23% propulsion boost</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-leaf"></i>
        <h4>ECO RUBBER</h4>
        <p>Sustainable sole tech</p>
    </div>
</div>

<div class="container">
    <h2 class="section-title"><i class="fas fa-compass"></i> SHOP BY CATEGORY</h2>
    <div class="category-grid">
        <a href="{{ route('men') }}" class="category-card">
            <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600" alt="men">
            <h3>MEN</h3>
        </a>
        <a href="{{ route('women') }}" class="category-card">
            <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600" alt="women">
            <h3>WOMEN</h3>
        </a>
        <a href="{{ route('kids') }}" class="category-card">
            <img src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600" alt="kids">
            <h3>KIDS</h3>
        </a>
        <a href="{{ route('sale') }}" class="category-card">
            <img src="https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600" alt="sale">
            <h3>SALE</h3>
        </a>
    </div>
</div>

<div class="cta-banner container">
    <h2>#CAPSTONE FUTURE</h2>
    <p>Get 15% off first order + exclusive early access</p>
    <a href="#" class="btn btn-red" onclick="alert('Welcome! 15% code: STRIDE15'); return false;">
        <i class="fas fa-gem"></i> JOIN NOW
    </a>
</div>
@endsection