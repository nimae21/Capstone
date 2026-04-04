@extends('layouts.app')

@section('content')
<!-- Font Awesome + Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ========== SAME FUTURISTIC DESIGN AS LOGIN & REGISTER ========== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Space Grotesk', sans-serif;
        background: #ffffff;
        overflow-x: hidden;
    }

    .forgot-outer {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffffff 0%, #f5f7fc 100%);
        overflow: hidden;
        padding: 2rem;
    }

    /* ===== FLOATING SHOES (same as login) ===== */
    .floating-shoes {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 0;
        pointer-events: none;
    }
    .floating-shoe {
        position: absolute;
        width: 120px;
        height: auto;
        opacity: 0.1;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.02));
        animation: floatGentle 25s infinite alternate ease-in-out;
        border-radius: 12px;
    }
    @keyframes floatGentle {
        0% { transform: translateY(0px) rotate(0deg); }
        100% { transform: translateY(-25px) rotate(2deg); }
    }
    /* 20 positions – spread evenly */
    .floating-shoe:nth-child(1) { top: 3%; left: 2%; width: 100px; animation-duration: 22s; }
    .floating-shoe:nth-child(2) { top: 8%; right: 3%; width: 130px; animation-duration: 26s; animation-delay: 1s; }
    .floating-shoe:nth-child(3) { bottom: 5%; left: 4%; width: 110px; animation-duration: 20s; animation-delay: 0.5s; }
    .floating-shoe:nth-child(4) { bottom: 12%; right: 5%; width: 140px; animation-duration: 28s; animation-delay: 2s; }
    .floating-shoe:nth-child(5) { top: 25%; left: 7%; width: 95px; animation-duration: 23s; animation-delay: 1.5s; }
    .floating-shoe:nth-child(6) { top: 45%; right: 6%; width: 125px; animation-duration: 25s; animation-delay: 0.8s; }
    .floating-shoe:nth-child(7) { top: 70%; left: 9%; width: 105px; animation-duration: 21s; animation-delay: 2.2s; }
    .floating-shoe:nth-child(8) { top: 85%; right: 8%; width: 115px; animation-duration: 27s; animation-delay: 1.2s; }
    .floating-shoe:nth-child(9) { top: 15%; left: 22%; width: 90px; animation-duration: 24s; animation-delay: 0.3s; }
    .floating-shoe:nth-child(10) { bottom: 30%; left: 14%; width: 130px; animation-duration: 22s; animation-delay: 2.5s; }
    .floating-shoe:nth-child(11) { top: 55%; left: 28%; width: 100px; animation-duration: 29s; animation-delay: 0.7s; }
    .floating-shoe:nth-child(12) { bottom: 60%; right: 18%; width: 120px; animation-duration: 23s; animation-delay: 1.8s; }
    .floating-shoe:nth-child(13) { top: 35%; right: 20%; width: 110px; animation-duration: 25s; animation-delay: 0.4s; }
    .floating-shoe:nth-child(14) { bottom: 45%; left: 20%; width: 95px; animation-duration: 26s; animation-delay: 2.1s; }
    .floating-shoe:nth-child(15) { top: 80%; left: 25%; width: 105px; animation-duration: 21s; animation-delay: 1.3s; }
    .floating-shoe:nth-child(16) { bottom: 8%; right: 28%; width: 125px; animation-duration: 28s; animation-delay: 0.9s; }
    .floating-shoe:nth-child(17) { top: 50%; left: 35%; width: 115px; animation-duration: 24s; animation-delay: 1.6s; }
    .floating-shoe:nth-child(18) { bottom: 75%; right: 30%; width: 100px; animation-duration: 26s; animation-delay: 0.2s; }
    .floating-shoe:nth-child(19) { top: 90%; left: 30%; width: 110px; animation-duration: 23s; animation-delay: 1.1s; }
    .floating-shoe:nth-child(20) { bottom: 18%; left: 38%; width: 120px; animation-duration: 27s; animation-delay: 2.3s; }

    /* ===== MOUSE GLOW (subtle) ===== */
    .mouse-glow {
        position: fixed;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(229,62,62,0.04) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        transform: translate(-50%, -50%);
        transition: transform 0.08s linear;
        z-index: 999;
    }

    /* ===== SUBTLE PARTICLES ===== */
    .particle-field {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 1;
        pointer-events: none;
    }
    .particle {
        position: absolute;
        background: rgba(229,62,62,0.08);
        border-radius: 50%;
        animation: particleRise 20s infinite linear;
    }
    @keyframes particleRise {
        0% { transform: translateY(100vh); opacity: 0; }
        20% { opacity: 0.4; }
        80% { opacity: 0.4; }
        100% { transform: translateY(-20vh); opacity: 0; }
    }

    /* ===== MAIN CARD (same as login) ===== */
    .forgot-container {
        position: relative;
        z-index: 20;
        width: 100%;
        max-width: 460px;
        margin: 2rem;
        animation: cardFade 0.5s ease;
    }
    @keyframes cardFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Soft moving border */
    .soft-border {
        position: absolute;
        inset: -2px;
        border-radius: 2rem;
        background: linear-gradient(90deg, 
            transparent 0%, 
            rgba(229,62,62,0.4) 25%, 
            rgba(255,140,140,0.6) 50%, 
            rgba(229,62,62,0.4) 75%, 
            transparent 100%);
        background-size: 200% 100%;
        animation: softSlide 4s linear infinite;
        z-index: 0;
        opacity: 0.4;
        filter: blur(4px);
    }
    @keyframes softSlide {
        0% { background-position: 0% 0%; }
        100% { background-position: 200% 0%; }
    }

    .forgot-card {
        position: relative;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(8px);
        border-radius: 2rem;
        padding: 2rem;
        z-index: 2;
        box-shadow: 0 15px 30px -12px rgba(0,0,0,0.05);
        border: 1px solid rgba(229,62,62,0.1);
        transition: box-shadow 0.2s;
    }
    .forgot-card:hover {
        box-shadow: 0 20px 35px -12px rgba(229,62,62,0.08);
        border-color: rgba(229,62,62,0.2);
    }

    /* Header */
    .forgot-header {
        text-align: center;
        margin-bottom: 1.8rem;
    }
    .forgot-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #0a0a0f, #e53e3e);
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
    }
    .forgot-header p {
        color: #6c6c7a;
        margin-top: 0.5rem;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    /* Session status message */
    .session-status {
        background: rgba(16, 185, 129, 0.1);
        border-left: 3px solid #10b981;
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.85rem;
        color: #065f46;
    }

    /* Form fields */
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.4rem;
        font-weight: 600;
        color: #1e1e2a;
        font-size: 0.8rem;
        transition: color 0.2s;
    }
    .form-group:focus-within label {
        color: #e53e3e;
    }
    .form-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        background: #f8f9fc;
        border: 1.5px solid #e2e6ea;
        border-radius: 60px;
        font-family: inherit;
        font-size: 0.9rem;
        transition: all 0.2s;
        outline: none;
    }
    .form-group input:focus {
        border-color: #e53e3e;
        box-shadow: 0 0 0 3px rgba(229,62,62,0.08);
        background: white;
    }
    .invalid-feedback {
        color: #e53e3e;
        font-size: 0.7rem;
        margin-top: 0.3rem;
        display: block;
    }

    /* Button */
    .btn-reset {
        width: 100%;
        padding: 0.85rem;
        background: #e53e3e;
        border: none;
        border-radius: 60px;
        font-weight: 700;
        font-size: 0.95rem;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .btn-reset::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    .btn-reset:hover::before {
        left: 100%;
    }
    .btn-reset:hover {
        background: #b91c1c;
        box-shadow: 0 4px 12px rgba(229,62,62,0.2);
    }

    /* Back to login link */
    .back-link {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #eceef2;
    }
    .back-link a {
        color: #e53e3e;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.85rem;
        transition: 0.2s;
    }
    .back-link a:hover {
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .forgot-card { padding: 1.5rem; }
        .floating-shoe { width: 70px; opacity: 0.08; }
        .soft-border { filter: blur(2px); }
        .mouse-glow { width: 200px; height: 200px; }
    }
</style>

<div class="forgot-outer">
    <!-- 20 floating shoes -->
    <div class="floating-shoes">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1605348532760-6e3b6d16a0b4?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1556906781-9a412961c28c?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1605348532760-6e3b6d16a0b4?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1556906781-9a412961c28c?w=200" alt="shoe">
        <img class="floating-shoe" src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=200" alt="shoe">
    </div>

    <!-- Particles -->
    <div class="particle-field" id="particleField"></div>

    <!-- Mouse glow -->
    <div class="mouse-glow" id="mouseGlow"></div>

    <!-- Forgot password card -->
    <div class="forgot-container">
        <div class="soft-border"></div>
        <div class="forgot-card">
            <div class="forgot-header">
                <h2>RESET PASSWORD</h2>
                <p>Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.</p>
            </div>

            <!-- Session Status (success message) -->
            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email">{{ __('Email Address') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <button type="submit" class="btn-reset">{{ __('SEND RESET LINK →') }}</button>

                <div class="back-link">
                    <a href="{{ route('login') }}">← Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Mouse glow follow
    const glow = document.getElementById('mouseGlow');
    if (glow) {
        document.addEventListener('mousemove', (e) => {
            glow.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
        });
        document.addEventListener('mouseleave', () => {
            glow.style.transform = `translate(-100px, -100px)`;
        });
    }

    // Particles – 60 subtle dots
    function createParticles() {
        const container = document.getElementById('particleField');
        if (!container) return;
        for (let i = 0; i < 60; i++) {
            const p = document.createElement('div');
            p.classList.add('particle');
            const size = Math.random() * 3 + 1;
            p.style.width = `${size}px`;
            p.style.height = `${size}px`;
            p.style.left = `${Math.random() * 100}%`;
            p.style.animationDuration = `${Math.random() * 15 + 12}s`;
            p.style.animationDelay = `${Math.random() * 8}s`;
            container.appendChild(p);
        }
    }
    createParticles();
</script>
@endsection