@extends('layouts.app')
@section('content')
<!-- Font Awesome + Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Space Grotesk', sans-serif;
        background: #f5f7fc;
        overflow-x: hidden;
    }

    /* Main container */
    .login-outer {
        position: relative;
        min-height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    /* Brighter background image (white-themed court) */
    .bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
        filter: brightness(1.05) contrast(1.02) saturate(0.95);
        transition: transform 0.4s ease-out;
    }

    /* Lighter overlay to keep white card readable */
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.75);
        z-index: 1;
    }

    /* Subtle court lines */
    .court-lines {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
        pointer-events: none;
        opacity: 0.2;
    }
    .court-lines svg {
        width: 100%;
        height: 100%;
        animation: slowPan 25s infinite alternate;
    }
    @keyframes slowPan {
        0% { transform: scale(1) translateX(0); }
        100% { transform: scale(1.03) translateX(0.5%); }
    }

    /* Floating basketballs - lighter red */
    .floating-balls {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
        pointer-events: none;
    }
    .floating-ball {
        position: absolute;
        color: #e53e3e;
        opacity: 0.15;
        font-size: 2.2rem;
        animation: floatBall 30s linear infinite;
    }
    @keyframes floatBall {
        0% { transform: translateY(120vh) rotate(0deg); opacity: 0; }
        15% { opacity: 0.15; }
        85% { opacity: 0.15; }
        100% { transform: translateY(-40vh) rotate(720deg); opacity: 0; }
    }

    /* Mouse glow - soft red */
    .mouse-glow {
        position: fixed;
        width: 550px;
        height: 550px;
        background: radial-gradient(circle, rgba(229,62,62,0.08) 0%, rgba(229,62,62,0.01) 60%, transparent 85%);
        border-radius: 50%;
        pointer-events: none;
        transform: translate(-50%, -50%);
        transition: transform 0.03s linear;
        z-index: 3;
        will-change: transform;
    }

    /* Login container - standard width but thicker form */
    .login-container {
        position: relative;
        z-index: 15;
        width: 100%;
        max-width: 460px;
        padding: 1.5rem;
        perspective: 800px;
    }

    /* Elegant white card - thicker padding */
    .login-card {
        background: #ffffff;
        border-radius: 48px;
        padding: 3rem 2.5rem;
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1), box-shadow 0.3s ease;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .login-card:hover {
        box-shadow: 0 35px 70px -15px rgba(229,62,62,0.2);
        transform: translateY(-4px);
    }

    /* Red accent border */
    .login-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 48px;
        padding: 2px;
        background: linear-gradient(135deg, #e53e3e, #ff8c42);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    .login-card:hover::after {
        opacity: 0.6;
    }

    /* Header */
    .login-header {
        text-align: center;
        margin-bottom: 2.2rem;
    }
    .header-icon {
        font-size: 4rem;
        color: #e53e3e;
        margin-bottom: 0.8rem;
        display: inline-block;
        animation: gentleFloat 3s infinite ease-in-out;
    }
    @keyframes gentleFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    .login-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #111;
        letter-spacing: -0.3px;
        margin-bottom: 0.4rem;
    }
    .login-header p {
        color: #5a5a6e;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Form groups - thicker inputs */
    .form-group {
        margin-bottom: 1.6rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.6rem;
        font-weight: 600;
        color: #1e1e2a;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }
    .input-wrapper {
        position: relative;
    }
    .input-wrapper input,
    .password-wrapper input {
        width: 100%;
        padding: 1rem 1.2rem 1rem 3rem;
        background: #f8f9fc;
        border: 1.5px solid #e9ecef;
        border-radius: 60px;
        font-size: 1rem;
        color: #111;
        transition: all 0.2s;
        font-weight: 500;
    }
    .input-wrapper input:focus,
    .password-wrapper input:focus {
        border-color: #e53e3e;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(229,62,62,0.1);
        outline: none;
    }
    .input-icon {
        position: absolute;
        left: 1.2rem;
        top: 50%;
        transform: translateY(-50%);
        color: #e53e3e;
        font-size: 1.2rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .input-wrapper:focus-within .input-icon {
        opacity: 1;
    }

    .toggle-password {
        position: absolute;
        right: 1.2rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #aaa;
        font-size: 1.1rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .toggle-password:hover { color: #e53e3e; }

    .invalid-feedback {
        color: #e53e3e;
        font-size: 0.75rem;
        margin-top: 0.4rem;
        padding-left: 1rem;
    }

    /* Options row */
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1.6rem 0 2rem;
        font-size: 0.85rem;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        color: #2c2c3a;
        font-weight: 500;
    }
    .checkbox-label input {
        width: 16px;
        height: 16px;
        accent-color: #e53e3e;
        cursor: pointer;
    }
    .forgot-link {
        color: #e53e3e;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }
    .forgot-link:hover { color: #b91c1c; text-decoration: underline; }

    /* Red button - thicker */
    .btn-login {
        width: 100%;
        padding: 1rem;
        background: #e53e3e;
        color: white;
        border: none;
        border-radius: 60px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 8px 18px -6px rgba(229,62,62,0.4);
        position: relative;
        overflow: hidden;
    }
    .btn-login:hover {
        background: #c53030;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -8px rgba(229,62,62,0.5);
    }
    .btn-login:active {
        transform: translateY(1px);
    }
    /* Ripple */
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        transform: scale(0);
        animation: rippleAnim 0.5s linear;
        pointer-events: none;
    }
    @keyframes rippleAnim {
        to { transform: scale(4); opacity: 0; }
    }

    /* Register link - "Join the league" */
    .register-link {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.4rem;
        border-top: 1px solid #edf2f7;
        color: #4a4a5a;
        font-size: 0.85rem;
    }
    .register-link a {
        color: #e53e3e;
        font-weight: 700;
        text-decoration: none;
        margin-left: 4px;
    }
    .register-link a:hover { text-decoration: underline; }

    /* Responsive */
    @media (max-width: 480px) {
        .login-card { padding: 2rem 1.5rem; }
        .login-header h2 { font-size: 1.8rem; }
        .login-container { padding: 0.8rem; }
        .mouse-glow { display: none; }
    }
</style>

<div class="login-outer">
    <!-- Brighter basketball background (light hardwood court) -->
    <img class="bg-image" 
         src="https://images.unsplash.com/photo-1600679472829-3044539ce8ed?auto=compress&cs=tinysrgb&w=2000" 
         alt="Bright Basketball Court">

    <div class="overlay"></div>

    <!-- Court lines -->
    <div class="court-lines">
        <svg viewBox="0 0 1200 800" preserveAspectRatio="none">
            <path d="M0 400 L1200 400 M400 0 L400 800 M800 0 L800 800" stroke="#e53e3e" stroke-width="1.5" fill="none" stroke-dasharray="8 8" opacity="0.4"/>
            <circle cx="600" cy="400" r="120" stroke="#e53e3e" stroke-width="1.2" fill="none" stroke-dasharray="5 6" opacity="0.3"/>
        </svg>
    </div>

    <!-- Floating balls -->
    <div class="floating-balls">
        <i class="fas fa-basketball-ball floating-ball" style="left: 10%; animation-duration: 28s; animation-delay: -3s;"></i>
        <i class="fas fa-basketball-ball floating-ball" style="left: 45%; animation-duration: 34s; animation-delay: -12s; font-size: 2.4rem;"></i>
        <i class="fas fa-basketball-ball floating-ball" style="left: 75%; animation-duration: 30s; animation-delay: -6s;"></i>
        <i class="fas fa-basketball-ball floating-ball" style="left: 92%; animation-duration: 38s; animation-delay: -20s; font-size: 1.8rem;"></i>
    </div>

    <!-- Mouse glow -->
    <div class="mouse-glow" id="mouseGlow"></div>

    <div class="login-container">
        <div class="login-card" id="tiltCard">
            <div class="login-header">
                <div class="header-icon">
                    <i class="fas fa-basketball-ball"></i>
                </div>
                <h2>HOOP ZONE</h2>
                <p>Welcome back — sign in to continue</p>
            </div>

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" type="email" class="@error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="hello@example.com">
                    </div>
                    @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input id="password" type="password" class="@error('password') is-invalid @enderror" 
                               name="password" required autocomplete="current-password" placeholder="••••••••">
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Keep me signed in</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span>LOG IN →</span>
                </button>

                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Join the league</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        // Toggle password visibility
        const toggleBtns = document.querySelectorAll('.toggle-password');
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const wrapper = this.closest('.password-wrapper');
                const input = wrapper.querySelector('input');
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });

        // Smooth mouse glow with lerp
        const glow = document.getElementById('mouseGlow');
        if (glow) {
            let mouseX = 0, mouseY = 0;
            let glowX = 0, glowY = 0;
            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });
            function animateGlow() {
                glowX += (mouseX - glowX) * 0.15;
                glowY += (mouseY - glowY) * 0.15;
                glow.style.transform = `translate(${glowX}px, ${glowY}px)`;
                requestAnimationFrame(animateGlow);
            }
            animateGlow();
        }

        // Parallax background
        const bg = document.querySelector('.bg-image');
        if (bg) {
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth) * 6 - 3;
                const y = (e.clientY / window.innerHeight) * 6 - 3;
                bg.style.transform = `translate(${x}px, ${y}px)`;
            });
        }

        // 3D tilt effect (smooth)
        const card = document.getElementById('tiltCard');
        if (card) {
            const container = document.querySelector('.login-container');
            let bounds = card.getBoundingClientRect();
            let targetRotateX = 0, targetRotateY = 0;
            let currentRotateX = 0, currentRotateY = 0;

            function updateBounds() {
                bounds = card.getBoundingClientRect();
            }
            window.addEventListener('resize', updateBounds);
            updateBounds();

            container.addEventListener('mousemove', (e) => {
                const x = e.clientX - bounds.left;
                const y = e.clientY - bounds.top;
                const centerX = bounds.width / 2;
                const centerY = bounds.height / 2;
                targetRotateY = ((x - centerX) / centerX) * 4;
                targetRotateX = ((y - centerY) / centerY) * -4;
            });
            container.addEventListener('mouseleave', () => {
                targetRotateX = 0;
                targetRotateY = 0;
            });

            function animateTilt() {
                currentRotateX += (targetRotateX - currentRotateX) * 0.12;
                currentRotateY += (targetRotateY - currentRotateY) * 0.12;
                card.style.transform = `rotateX(${currentRotateX}deg) rotateY(${currentRotateY}deg)`;
                requestAnimationFrame(animateTilt);
            }
            animateTilt();
        }

        // Ripple effect on button
        const btn = document.getElementById('loginBtn');
        if (btn) {
            btn.addEventListener('click', function(e) {
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const ripple = document.createElement('span');
                ripple.className = 'ripple';
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size/2}px`;
                ripple.style.top = `${e.clientY - rect.top - size/2}px`;
                btn.appendChild(ripple);
                setTimeout(() => ripple.remove(), 500);
            });
        }

        // Form loading state
        const form = document.getElementById('loginForm');
        if (form && btn) {
            form.addEventListener('submit', function() {
                if (!form.checkValidity()) return;
                btn.disabled = true;
                btn.innerHTML = `<i class="fas fa-spinner fa-pulse"></i><span>LOGGING IN...</span>`;
                btn.style.opacity = '0.8';
            });
        }

        // Floating balls hover interaction
        document.querySelectorAll('.floating-ball').forEach(ball => {
            ball.addEventListener('mouseenter', () => {
                ball.style.animationPlayState = 'paused';
                ball.style.opacity = '0.3';
            });
            ball.addEventListener('mouseleave', () => {
                ball.style.animationPlayState = 'running';
                ball.style.opacity = '';
            });
        });
    })();
</script>
@endsection