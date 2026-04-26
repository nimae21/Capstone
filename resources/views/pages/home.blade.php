@extends('layouts.pages')

@section('title', 'Achilles - Premium Performance Footwear')

@section('styles')
<style>
    body { background: #ffffff; }
    
    .hero {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
        padding: 3rem 0 5rem;
    }
    
    .hero-content h1 {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #1a1a2e, #dc2626);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .hero-content p {
        color: #64748b;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }
    
    .btn-red {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        padding: 0.875rem 2rem;
        border-radius: 9999px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 8px 20px -6px rgba(220,38,38,0.4);
    }
    
    .btn-red:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px -8px rgba(220,38,38,0.5);
    }
    
    .btn-outline {
        background: transparent;
        color: #1e293b;
        padding: 0.875rem 2rem;
        border-radius: 9999px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        border: 2px solid #e2e8f0;
    }
    
    .btn-outline:hover {
        border-color: #dc2626;
        color: #dc2626;
        transform: translateY(-2px);
    }
    
    .hero-features {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    
    .hero-features div {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .hero-features i {
        color: #dc2626;
    }
    
    .hero-image img {
        width: 100%;
        max-width: 500px;
        border-radius: 2rem;
        box-shadow: 0 25px 45px -12px rgba(0,0,0,0.2);
        transition: transform 0.3s ease;
    }
    
    .hero-image img:hover {
        transform: scale(1.02);
    }
    
    .features {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        padding: 4rem 0;
        margin: 2rem auto;
        background: #f8fafc;
        border-radius: 2rem;
    }
    
    .feature-item {
        text-align: center;
        padding: 1.5rem;
    }
    
    .feature-item i {
        font-size: 2.5rem;
        color: #dc2626;
        margin-bottom: 1rem;
    }
    
    .feature-item h4 {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }
    
    .feature-item p {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .section-title {
        font-size: 2rem;
        font-weight: 800;
        text-align: center;
        margin-bottom: 2.5rem;
        background: linear-gradient(135deg, #1e293b, #dc2626);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    
    .section-title i {
        color: #dc2626;
    }
    
    .category-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        padding-bottom: 4rem;
    }
    
    .category-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
        text-decoration: none;
        display: block;
        aspect-ratio: 1/1;
    }
    
    .category-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .category-card:hover img {
        transform: scale(1.1);
    }
    
    .category-card h3 {
        position: absolute;
        bottom: 1.5rem;
        left: 1.5rem;
        color: white;
        font-size: 1.5rem;
        font-weight: 800;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        z-index: 1;
    }
    
    .category-card::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50%;
        background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
        z-index: 0;
    }
    
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    @media (max-width: 968px) {
        .hero { grid-template-columns: 1fr; text-align: center; }
        .hero-features { justify-content: center; }
        .features { grid-template-columns: repeat(2, 1fr); }
        .category-grid { grid-template-columns: repeat(2, 1fr); }
        .hero-content h1 { font-size: 2.5rem; }
    }
    
    @media (max-width: 640px) {
        .features { grid-template-columns: 1fr; }
        .category-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="container hero">
    <div class="hero-content">
        <h1>RUN IN <span style="color:#dc2626;">Achilles</span></h1>
        <p>Limitless performance meets cybernetic design. Ultra-responsive cushioning, premium materials, and free shipping worldwide.</p>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('men') }}" class="btn-red">SHOP MEN</a>
            <a href="{{ route('women') }}" class="btn-outline">SHOP WOMEN</a>
        </div>
        <div class="hero-features">
            <div><i class="fas fa-truck"></i> Free shipping</div>
            <div><i class="fas fa-rotate-left"></i> 60-day returns</div>
            <div><i class="fas fa-shield-alt"></i> Authentic Guarantee</div>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" alt="Achilles shoe">
    </div>
</div>

<div class="features container">
    <div class="feature-item">
        <i class="fas fa-crown"></i>
        <h4>PREMIUM</h4>
        <p>Italian leather & mesh</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-wind"></i>
        <h4>BREATHABLE</h4>
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
@endsection