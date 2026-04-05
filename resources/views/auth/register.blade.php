@extends('layouts.app')

@section('content')
<!-- ========================================================================
     CINEMATIC REGISTER – AIR JORDAN 1 CHICAGO BULLS
     Same premium design as login | Darker cinematic background
     Fixed floating labels for password fields
     ======================================================================== -->
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    /* ---------- RESET & FULLSCREEN ---------- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
        min-height: 100vh;
        background: #0a0a0a;
    }

    /* ========== FULLSCREEN CINEMATIC BACKGROUND (darker, like login) ========== */
    .cinematic-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        overflow: hidden;
        background: #0a0a0a;
    }

    /* Jordan 1 on darker background – more dramatic */
    .bg-jordan {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=2000&auto=format');
        background-size: cover;
        background-position: center 40%;
        background-repeat: no-repeat;
        transform: scale(1.08);
        animation: cinematicZoom 25s infinite alternate ease-in-out;
        filter: brightness(0.85) contrast(1.1) saturate(1.05);
        opacity: 0.4;
    }

    @keyframes cinematicZoom {
        0% { transform: scale(1.08); opacity: 0.35; filter: brightness(0.85) contrast(1.1); }
        100% { transform: scale(1.22); opacity: 0.5; filter: brightness(0.92) contrast(1.18); }
    }

    /* dark overlay for depth (stronger) */
    .bg-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 70% 40%, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);
        z-index: 1;
    }

    /* sweeping light rays (cinematic) */
    .light-sweep {
        position: absolute;
        top: -30%;
        left: -30%;
        width: 160%;
        height: 160%;
        background: linear-gradient(115deg, rgba(230,0,35,0.08) 0%, rgba(255,255,255,0) 60%);
        transform: rotate(35deg);
        animation: sweepGlide 14s infinite cubic-bezier(0.45, 0.05, 0.2, 0.99);
        z-index: 2;
        pointer-events: none;
    }

    @keyframes sweepGlide {
        0% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.2; }
        50% { transform: rotate(35deg) translateX(20%) translateY(20%); opacity: 0.5; }
        100% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.2; }
    }

    /* red pulse – Chicago energy */
    .red-pulse {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 40% 60%, rgba(230,0,35,0.15) 0%, transparent 70%);
        animation: breatheRed 9s infinite alternate;
        z-index: 2;
        pointer-events: none;
    }

    @keyframes breatheRed {
        0% { opacity: 0.15; transform: scale(1); }
        100% { opacity: 0.4; transform: scale(1.2); }
    }

    /* floating particles (elegant dust) - interactive on hover */
    .particle-elegant {
        position: absolute;
        background: rgba(230,0,35,0.3);
        border-radius: 50%;
        filter: blur(2px);
        pointer-events: auto;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.2);
        animation: floatSlow 15s infinite alternate;
        z-index: 2;
    }

    .particle-elegant:hover {
        transform: scale(3) !important;
        background: rgba(230,0,35,0.7);
        filter: blur(1px);
        box-shadow: 0 0 20px rgba(230,0,35,0.9);
        transition: transform 0.15s ease-out;
    }

    @keyframes floatSlow {
        0% { transform: translate(0,0) scale(1); opacity: 0.1; }
        100% { transform: translate(30px, -40px) scale(1.4); opacity: 0.4; }
    }

    /* film grain overlay (cinematic texture) */
    .film-grain {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIj48ZmlsdGVyIGlkPSJmIj48ZmVUdXJidWxlbmNlIHR5cGU9ImZyYWN0YWxOb2lzZSIgYmFzZUZyZXF1ZW5jeT0iLjUiIG51bU9jdGF2ZXM9IjMiLz48L2ZpbHRlcj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWx0ZXI9InVybCgjZikiIG9wYWNpdHk9IjAuMDgiLz48L3N2Zz4=');
        background-repeat: repeat;
        opacity: 0.12;
        pointer-events: none;
        z-index: 3;
        mix-blend-mode: overlay;
    }

    /* strong vignette (like login) */
    .vignette-cinema {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, transparent 55%, rgba(0,0,0,0.65) 100%);
        z-index: 3;
        pointer-events: none;
    }

    /* digital micro-grid (barely visible) */
    .micro-grid {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: 
            linear-gradient(rgba(230,0,35,0.02) 1px, transparent 1px),
            linear-gradient(90deg, rgba(230,0,35,0.02) 1px, transparent 1px);
        background-size: 45px 45px;
        pointer-events: none;
        z-index: 2;
        animation: gridDrift 24s infinite linear;
    }

    @keyframes gridDrift {
        0% { transform: translate(0,0); }
        100% { transform: translate(45px, 45px); }
    }

    /* ========== FORM CONTAINER – CENTERED & CLEAN ========== */
    .form-wrapper {
        position: relative;
        z-index: 20;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        perspective: 1300px;
    }

    /* Clean 3D Glass Card (lighter on dark bg) */
    .glass-card-clean {
        width: 100%;
        max-width: 560px;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(16px);
        border-radius: 48px;
        padding: 2.5rem 2rem;
        transform-style: preserve-3d;
        transform: rotateX(1.5deg) rotateY(0.8deg) translateZ(15px);
        box-shadow: 
            0 30px 55px -20px rgba(0,0,0,0.4),
            0 0 0 1px rgba(255,255,255,0.7),
            0 0 0 2.5px rgba(230,0,35,0.12),
            inset 0 1px 0 rgba(255,255,255,0.9);
        transition: all 0.4s cubic-bezier(0.2, 0.95, 0.4, 1.1);
        animation: gentleFloat 5.5s infinite ease-in-out, entranceRise 0.9s ease-out 0.2s backwards;
    }

    @keyframes gentleFloat {
        0% { transform: rotateX(1.5deg) rotateY(0.8deg) translateZ(15px) translateY(0px); }
        50% { transform: rotateX(1.5deg) rotateY(0.8deg) translateZ(22px) translateY(-6px); }
        100% { transform: rotateX(1.5deg) rotateY(0.8deg) translateZ(15px) translateY(0px); }
    }

    @keyframes entranceRise {
        from { opacity: 0; transform: translateY(50px) rotateX(4deg) scale(0.96); filter: blur(6px); }
        to { opacity: 1; transform: rotateX(1.5deg) rotateY(0.8deg) translateZ(15px) scale(1); filter: blur(0); }
    }

    .glass-card-clean:hover {
        transform: rotateX(0.5deg) rotateY(2deg) translateZ(32px) scale(1.01);
        box-shadow: 
            0 45px 70px -18px rgba(230,0,35,0.3),
            0 0 0 1px rgba(255,255,255,0.9),
            0 0 0 4px rgba(230,0,35,0.2),
            inset 0 1px 0 rgba(255,255,255,1);
    }

    /* inner shimmer */
    .glass-card-clean::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 48px;
        background: linear-gradient(125deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 55%);
        pointer-events: none;
    }

    .form-header {
        text-align: center;
        margin-bottom: 1.8rem;
        transform: translateZ(8px);
    }

    /* Aesthetic shoe icon – animated & interactive */
    .logo-badge {
        font-size: 3rem;
        color: #E60023;
        margin-bottom: 0.5rem;
        display: inline-block;
        animation: iconFloat 3s infinite ease-in-out;
        filter: drop-shadow(0 0 6px rgba(230,0,35,0.3));
        transition: all 0.25s cubic-bezier(0.2, 1.2, 0.4, 1);
    }

    .logo-badge:hover {
        transform: scale(1.15) rotate(5deg);
        filter: drop-shadow(0 0 18px rgba(230,0,35,0.6));
        animation: none;
    }

    @keyframes iconFloat {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-6px); }
        100% { transform: translateY(0px); }
    }

    .form-header h2 {
        font-size: 2.2rem;
        font-weight: 800;
        font-family: 'Space Grotesk', sans-serif;
        background: linear-gradient(125deg, #000 25%, #E60023 85%);
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        letter-spacing: -0.02em;
    }

    .form-header p {
        color: #4f4f62;
        font-weight: 500;
        font-size: 0.85rem;
    }

    /* Two-column layout for name fields */
    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.2rem;
        flex-wrap: wrap;
    }
    .form-row .input-group-clean {
        flex: 1;
        margin-bottom: 0;
    }

    /* Clean Input Fields with floating label */
    .input-group-clean {
        margin-bottom: 1.2rem;
        position: relative;
        transition: transform 0.2s;
    }

    .input-group-clean:hover {
        transform: translateX(3px);
    }

    .floating-label-clean {
        position: relative;
        width: 100%;
    }

    .floating-label-clean input {
        width: 100%;
        padding: 1.15rem 1rem 0.55rem 1rem;
        background: #FFFFFF;
        border: 1.2px solid rgba(0,0,0,0.08);
        border-radius: 36px;
        font-size: 0.95rem;
        font-weight: 500;
        color: #111;
        transition: all 0.25s ease;
        font-family: 'Inter', sans-serif;
        outline: none;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .floating-label-clean input:focus-visible {
        outline: 2px solid #E60023;
        outline-offset: 2px;
    }

    .floating-label-clean input:hover {
        border-color: rgba(230,0,35,0.4);
        background: #fefefe;
    }

    .floating-label-clean input:focus {
        border-color: #E60023;
        box-shadow: 0 0 0 4px rgba(230,0,35,0.1), 0 6px 14px rgba(0,0,0,0.03);
        transform: scale(1.005);
    }

    /* Label styling - will be controlled by focus and has-value class */
    .floating-label-clean label {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
        color: #9a9fb0;
        pointer-events: none;
        transition: 0.2s ease;
        background: transparent;
        padding: 0 3px;
        font-weight: 500;
        z-index: 1;
    }

    /* Focus state for label */
    .floating-label-clean:focus-within label {
        top: 0.2rem;
        transform: translateY(0);
        font-size: 0.65rem;
        color: #E60023;
        font-weight: 700;
        background: transparent;
    }

    /* Has value state (applied by JS for all fields including password) */
    .floating-label-clean.has-value label {
        top: 0.2rem;
        transform: translateY(0);
        font-size: 0.65rem;
        color: #E60023;
        font-weight: 700;
        background: transparent;
    }

    /* Password wrapper with toggle */
    .password-wrapper {
        position: relative;
        width: 100%;
    }
    .password-wrapper input {
        padding-right: 2.8rem;
    }
    .password-toggle-clean {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #b0b4c4;
        cursor: pointer;
        font-size: 1rem;
        transition: 0.2s;
        z-index: 2;
        padding: 0.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .password-toggle-clean:hover { color: #E60023; transform: translateY(-50%) scale(1.1); }
    .password-toggle-clean:focus-visible {
        outline: 2px solid #E60023;
        outline-offset: 2px;
        border-radius: 50%;
    }

    /* error minimal */
    .input-group-clean.error .floating-label-clean input {
        border-color: #E60023;
        background: #FFF8F8;
        animation: gentleShake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }

    @keyframes gentleShake {
        0%,100% { transform: translateX(0); }
        20% { transform: translateX(-4px); }
        40% { transform: translateX(4px); }
        60% { transform: translateX(-2px); }
        80% { transform: translateX(2px); }
    }

    .error-message-clean {
        font-size: 0.68rem;
        color: #E60023;
        margin-top: 0.35rem;
        margin-left: 1rem;
        font-weight: 600;
    }

    /* Premium Button */
    .btn-premium {
        width: 100%;
        background: #E60023;
        border: none;
        padding: 0.95rem;
        border-radius: 60px;
        font-weight: 800;
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
        color: white;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.2, 1.2, 0.4, 1), box-shadow 0.3s, background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        box-shadow: 0 10px 22px -10px rgba(230,0,35,0.5);
        position: relative;
        overflow: hidden;
        letter-spacing: 0.3px;
        transform: translateZ(8px);
        margin-top: 0.5rem;
    }

    .btn-premium:hover {
        background: #C2001F;
        transform: scale(1.02) translateZ(12px);
        box-shadow: 0 18px 30px -12px rgba(230,0,35,0.7);
        letter-spacing: 1px;
    }

    .btn-premium:active { transform: scale(0.98) translateZ(4px); }
    .btn-premium:focus-visible {
        outline: 2px solid #E60023;
        outline-offset: 3px;
    }

    .ripple-clean {
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.7);
        transform: scale(0);
        animation: rippleClean 0.5s linear;
        pointer-events: none;
    }

    @keyframes rippleClean {
        to { transform: scale(7); opacity: 0; }
    }

    .btn-premium.loading {
        pointer-events: none;
        opacity: 0.9;
    }

    .login-link {
        text-align: center;
        margin-top: 1.8rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: 0.8rem;
        font-weight: 500;
        transition: 0.2s;
    }

    .login-link a {
        color: #E60023;
        font-weight: 800;
        text-decoration: none;
        transition: 0.2s;
    }

    .login-link a:hover {
        text-decoration: underline;
        letter-spacing: 0.3px;
    }
    .login-link a:focus-visible {
        outline: 2px solid #E60023;
        outline-offset: 2px;
        border-radius: 4px;
    }

    /* responsive */
    @media (max-width: 768px) {
        .form-wrapper { padding: 1.5rem; }
        .glass-card-clean { padding: 2rem 1.5rem; max-width: 480px; }
        .form-header h2 { font-size: 2rem; }
        .form-row { flex-direction: column; gap: 0.8rem; }
    }

    @media (max-width: 480px) {
        .glass-card-clean { padding: 1.6rem 1.2rem; }
        .form-header h2 { font-size: 1.8rem; }
        .btn-premium { padding: 0.8rem; }
    }
</style>

<!-- FULLSCREEN CINEMATIC BACKGROUND (dark, dramatic) -->
<div class="cinematic-bg">
    <div class="bg-jordan"></div>
    <div class="bg-overlay"></div>
    <div class="light-sweep"></div>
    <div class="red-pulse"></div>
    <div class="micro-grid"></div>
    <div class="film-grain"></div>
    <div class="vignette-cinema"></div>
    <div id="elegantParticles"></div>
</div>

<!-- REGISTER FORM -->
<div class="form-wrapper" id="form3dContainer">
    <div class="glass-card-clean" id="cleanGlassCard">
        <div class="form-header">
            <div class="logo-badge"><i class="fas fa-shoe-prints" aria-hidden="true"></i></div>
            <h2>CREATE ACCOUNT</h2>
            <p>join the movement – wear your weakness</p>
        </div>

        <form method="POST" action="{{ route('register') }}" id="premiumRegisterForm">
            @csrf

            <!-- Row: First Name + Middle Name -->
            <div class="form-row">
                <div class="input-group-clean @error('first_name') error @enderror">
                    <div class="floating-label-clean">
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder=" " required autofocus>
                        <label for="first_name">First Name</label>
                    </div>
                    @error('first_name') <div class="error-message-clean">{{ $message }}</div> @enderror
                </div>

                <div class="input-group-clean @error('middle_name') error @enderror">
                    <div class="floating-label-clean">
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder=" ">
                        <label for="middle_name">Middle Name</label>
                    </div>
                    @error('middle_name') <div class="error-message-clean">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Row: Last Name + Suffix -->
            <div class="form-row">
                <div class="input-group-clean @error('last_name') error @enderror">
                    <div class="floating-label-clean">
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder=" " required>
                        <label for="last_name">Last Name</label>
                    </div>
                    @error('last_name') <div class="error-message-clean">{{ $message }}</div> @enderror
                </div>

                <div class="input-group-clean @error('suffix') error @enderror">
                    <div class="floating-label-clean">
                        <input type="text" id="suffix" name="suffix" value="{{ old('suffix') }}" placeholder=" ">
                        <label for="suffix">Suffix</label>
                    </div>
                    @error('suffix') <div class="error-message-clean">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="input-group-clean @error('email') error @enderror">
                <div class="floating-label-clean">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" " required>
                    <label for="email">Email Address</label>
                </div>
                @error('email') <div class="error-message-clean">{{ $message }}</div> @enderror
            </div>

            <!-- Password -->
            <div class="input-group-clean @error('password') error @enderror">
                <div class="floating-label-clean">
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder=" " required>
                        <button type="button" class="password-toggle-clean" aria-label="Toggle password visibility" tabindex="0">
                            <i class="fas fa-eye-slash" aria-hidden="true"></i>
                        </button>
                    </div>
                    <label for="password">Password</label>
                </div>
                @error('password') <div class="error-message-clean">{{ $message }}</div> @enderror
            </div>

            <!-- Confirm Password -->
            <div class="input-group-clean">
                <div class="floating-label-clean">
                    <div class="password-wrapper">
                        <input type="password" id="password-confirm" name="password_confirmation" placeholder=" " required>
                        <button type="button" class="password-toggle-clean" aria-label="Toggle password visibility" tabindex="0">
                            <i class="fas fa-eye-slash" aria-hidden="true"></i>
                        </button>
                    </div>
                    <label for="password-confirm">Confirm Password</label>
                </div>
            </div>

            <button type="submit" class="btn-premium" id="premiumRegisterBtn" aria-label="Register new account">
                <span>REGISTER →</span>
            </button>

            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}" aria-label="Go to login page">Login</a>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // ========== 1. ELEGANT PARTICLES (cinematic dust) with hover ==========
        const particleField = document.getElementById('elegantParticles');
        if(particleField) {
            for(let i = 0; i < 180; i++) {
                const p = document.createElement('div');
                p.classList.add('particle-elegant');
                const size = Math.random() * 7 + 1.5;
                p.style.width = `${size}px`;
                p.style.height = `${size}px`;
                p.style.left = `${Math.random() * 100}%`;
                p.style.top = `${Math.random() * 100}%`;
                p.style.animationDuration = `${Math.random() * 20 + 10}s`;
                p.style.animationDelay = `${Math.random() * 12}s`;
                
                p.addEventListener('mouseenter', (e) => {
                    e.target.style.transform = `scale(3.5)`;
                    e.target.style.transition = 'transform 0.1s ease-out';
                });
                p.addEventListener('mouseleave', (e) => {
                    e.target.style.transform = '';
                });
                particleField.appendChild(p);
            }
        }

        // ========== 2. DEEP PARALLAX FOR BACKGROUND ==========
        const bgJordan = document.querySelector('.bg-jordan');
        if(bgJordan) {
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth) * 24 - 12;
                const y = (e.clientY / window.innerHeight) * 16 - 8;
                bgJordan.style.transform = `translate(${x * -0.6}px, ${y * -0.4}px) scale(1.12)`;
            });
        }

        // ========== 3. 3D TILT FOR FORM CARD ==========
        const container = document.getElementById('form3dContainer');
        const card = document.getElementById('cleanGlassCard');
        if(container && card) {
            container.addEventListener('mousemove', (e) => {
                const rect = container.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                const rotateY = (x - 0.5) * 6;
                const rotateX = (y - 0.5) * -5;
                card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(22px)`;
            });
            container.addEventListener('mouseleave', () => {
                card.style.transform = 'rotateX(1.5deg) rotateY(0.8deg) translateZ(15px)';
            });
        }

        // ========== 4. FLOATING LABEL FIX FOR ALL FIELDS (including password) ==========
        // This ensures that when an input has value, the label moves up.
        // Works for text, email, password, etc.
        function initFloatingLabels() {
            const floatingContainers = document.querySelectorAll('.floating-label-clean');
            floatingContainers.forEach(container => {
                const input = container.querySelector('input');
                if (!input) return;
                
                // Function to update has-value class
                function updateHasValue() {
                    if (input.value.trim() !== '') {
                        container.classList.add('has-value');
                    } else {
                        container.classList.remove('has-value');
                    }
                }
                
                // Initial check (for pre-filled values)
                updateHasValue();
                
                // Listen for input events
                input.addEventListener('input', updateHasValue);
                input.addEventListener('change', updateHasValue);
            });
        }
        
        // Run on page load and also after any dynamic changes (though none expected)
        initFloatingLabels();

        // ========== 5. PASSWORD TOGGLE (for both fields) with accessibility ==========
        document.querySelectorAll('.password-toggle-clean').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const wrapper = btn.closest('.password-wrapper');
                const input = wrapper.querySelector('input');
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    btn.setAttribute('aria-label', 'Hide password');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    btn.setAttribute('aria-label', 'Show password');
                }
            });
            btn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    btn.click();
                }
            });
        });

        // ========== 6. RIPPLE ON BUTTON ==========
        const registerBtn = document.getElementById('premiumRegisterBtn');
        function addRipple(e, btn) {
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const ripple = document.createElement('span');
            ripple.className = 'ripple-clean';
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size/2}px`;
            ripple.style.top = `${e.clientY - rect.top - size/2}px`;
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 500);
        }
        if(registerBtn) {
            registerBtn.addEventListener('click', (e) => {
                if(!registerBtn.disabled) addRipple(e, registerBtn);
            });
        }

        // ========== 7. CLIENT VALIDATION (basic) ==========
        const form = document.getElementById('premiumRegisterForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password-confirm');
        
        if(form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                // Clear previous custom errors
                document.querySelectorAll('.input-group-clean').forEach(g => g.classList.remove('error'));
                document.querySelectorAll('.error-message-clean:not(.server-error)').forEach(e => e.remove());
                
                // Email validation
                const emailVal = emailInput.value.trim();
                if(!emailVal || !/^\S+@\S+\.\S+$/.test(emailVal)) {
                    const group = emailInput.closest('.input-group-clean');
                    group.classList.add('error');
                    let errDiv = group.querySelector('.error-message-clean');
                    if(!errDiv) {
                        errDiv = document.createElement('div');
                        errDiv.className = 'error-message-clean';
                        group.appendChild(errDiv);
                    }
                    errDiv.textContent = 'Valid email required';
                    errDiv.style.display = 'block';
                    isValid = false;
                }
                
                // Password match
                if(passwordInput.value !== confirmInput.value) {
                    const group = confirmInput.closest('.input-group-clean');
                    group.classList.add('error');
                    let errDiv = group.querySelector('.error-message-clean');
                    if(!errDiv) {
                        errDiv = document.createElement('div');
                        errDiv.className = 'error-message-clean';
                        group.appendChild(errDiv);
                    }
                    errDiv.textContent = 'Passwords do not match';
                    errDiv.style.display = 'block';
                    isValid = false;
                }
                
                if(!isValid) {
                    e.preventDefault();
                    return;
                }
                
                registerBtn.disabled = true;
                registerBtn.classList.add('loading');
                registerBtn.innerHTML = `<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i><span>CREATING ACCOUNT...</span>`;
            });
        }
    })();
</script>
@endsection