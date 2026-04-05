@extends('layouts.app')

@section('content')
<!-- ========================================================================
     CINEMATIC LOGIN – AIR JORDAN 1 CHICAGO BULLS
     Full-screen immersive experience with next-gen animations
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

    /* ========== FULLSCREEN CINEMATIC BACKGROUND ========== */
    .cinematic-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        overflow: hidden;
    }

    /* Jordan 1 Chicago Bulls hero image */
    .bg-shoe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=2000&auto=format');
        background-size: cover;
        background-position: center 35%;
        background-repeat: no-repeat;
        transform: scale(1.08);
        animation: cinematicZoom 22s infinite alternate ease-in-out;
        filter: brightness(0.88) contrast(1.12) saturate(1.05);
    }

    @keyframes cinematicZoom {
        0% { transform: scale(1.08); filter: brightness(0.88) contrast(1.12); }
        100% { transform: scale(1.22); filter: brightness(0.92) contrast(1.18); }
    }

    /* dynamic overlays for depth */
    .bg-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 70% 40%, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.65) 100%);
        z-index: 1;
    }

    /* sweeping light rays (cinematic) */
    .light-sweep {
        position: absolute;
        top: -30%;
        left: -30%;
        width: 160%;
        height: 160%;
        background: linear-gradient(115deg, rgba(230,0,35,0.08) 0%, rgba(255,255,255,0) 50%);
        transform: rotate(35deg);
        animation: sweepGlide 14s infinite cubic-bezier(0.45, 0.05, 0.2, 0.99);
        z-index: 2;
        pointer-events: none;
    }

    @keyframes sweepGlide {
        0% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.3; }
        50% { transform: rotate(35deg) translateX(20%) translateY(20%); opacity: 0.7; }
        100% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.3; }
    }

    /* red pulse accent (Chicago Bulls energy) */
    .red-pulse {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 40% 60%, rgba(230,0,35,0.12) 0%, transparent 70%);
        animation: breatheRed 9s infinite alternate;
        z-index: 2;
        pointer-events: none;
    }

    @keyframes breatheRed {
        0% { opacity: 0.2; transform: scale(1); }
        100% { opacity: 0.6; transform: scale(1.15); }
    }

    /* floating particles (elegant dust) */
    .particle-elegant {
        position: absolute;
        background: rgba(230,0,35,0.3);
        border-radius: 50%;
        filter: blur(2px);
        pointer-events: none;
        animation: floatSlow 15s infinite alternate;
        z-index: 2;
    }

    @keyframes floatSlow {
        0% { transform: translate(0,0) scale(1); opacity: 0.1; }
        100% { transform: translate(25px, -35px) scale(1.4); opacity: 0.35; }
    }

    /* subtle vignette */
    .vignette-cinema {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, transparent 55%, rgba(0,0,0,0.55) 100%);
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

    /* Clean 3D Glass Card */
    .glass-card-clean {
        width: 100%;
        max-width: 500px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px);
        border-radius: 48px;
        padding: 2.5rem 2rem;
        transform-style: preserve-3d;
        transform: rotateX(1.5deg) rotateY(0.8deg) translateZ(15px);
        box-shadow: 
            0 30px 55px -20px rgba(0,0,0,0.35),
            0 0 0 1px rgba(255,255,255,0.65),
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
        transform: rotateX(0.5deg) rotateY(1.5deg) translateZ(28px);
        box-shadow: 
            0 40px 65px -18px rgba(230,0,35,0.3),
            0 0 0 1px rgba(255,255,255,0.85),
            0 0 0 3px rgba(230,0,35,0.2),
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
        background: linear-gradient(125deg, rgba(255,255,255,0.35) 0%, rgba(255,255,255,0) 55%);
        pointer-events: none;
    }

    .form-header {
        text-align: center;
        margin-bottom: 2rem;
        transform: translateZ(8px);
    }

    .logo-badge {
        font-size: 2.6rem;
        color: #E60023;
        margin-bottom: 0.5rem;
        animation: softGlow 2.8s infinite alternate;
        display: inline-block;
    }

    @keyframes softGlow {
        0% { transform: scale(1); text-shadow: 0 0 0 rgba(230,0,35,0); }
        100% { transform: scale(1.04); text-shadow: 0 0 12px rgba(230,0,35,0.35); }
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

    /* Clean Input Fields */
    .input-group-clean {
        margin-bottom: 1.6rem;
        position: relative;
    }

    .floating-label-clean {
        position: relative;
        width: 100%;
    }

    .floating-label-clean input {
        width: 100%;
        padding: 1.15rem 1rem 0.55rem 1rem;
        background: #FFFFFF;
        border: 1.2px solid rgba(0,0,0,0.1);
        border-radius: 36px;
        font-size: 0.95rem;
        font-weight: 500;
        color: #111;
        transition: all 0.25s ease;
        font-family: 'Inter', sans-serif;
        outline: none;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .floating-label-clean input:focus {
        border-color: #E60023;
        box-shadow: 0 0 0 4px rgba(230,0,35,0.1), 0 6px 14px rgba(0,0,0,0.03);
        transform: scale(1.005);
    }

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
    }

    .floating-label-clean input:focus ~ label,
    .floating-label-clean input:not(:placeholder-shown) ~ label {
        top: 0.2rem;
        transform: translateY(0);
        font-size: 0.65rem;
        color: #E60023;
        font-weight: 700;
        background: transparent;
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
    }

    .password-toggle-clean:hover { color: #E60023; }

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

    /* options */
    .form-options-clean {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0.8rem 0 1.8rem;
        font-size: 0.8rem;
    }

    .checkbox-clean {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        color: #2e2e3e;
    }

    .checkbox-clean input { accent-color: #E60023; width: 15px; height: 15px; }

    .forgot-link-clean {
        color: #E60023;
        font-weight: 700;
        text-decoration: none;
        transition: 0.2s;
    }

    .forgot-link-clean:hover { text-decoration: underline; opacity: 0.85; }

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
        transition: transform 0.2s cubic-bezier(0.2, 1.2, 0.4, 1), box-shadow 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        box-shadow: 0 10px 22px -10px rgba(230,0,35,0.5);
        position: relative;
        overflow: hidden;
        letter-spacing: 0.3px;
        transform: translateZ(8px);
    }

    .btn-premium:hover {
        background: #C2001F;
        transform: scale(1.01) translateZ(12px);
        box-shadow: 0 16px 28px -12px rgba(230,0,35,0.65);
    }

    .btn-premium:active { transform: scale(0.98) translateZ(4px); }

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

    .register-link-clean {
        text-align: center;
        margin-top: 1.8rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: 0.8rem;
        font-weight: 500;
    }

    .register-link-clean a {
        color: #E60023;
        font-weight: 800;
        text-decoration: none;
    }

    /* responsive */
    @media (max-width: 768px) {
        .form-wrapper { padding: 1.5rem; }
        .glass-card-clean { padding: 2rem 1.5rem; max-width: 440px; }
        .form-header h2 { font-size: 2rem; }
    }

    @media (max-width: 480px) {
        .glass-card-clean { padding: 1.6rem 1.2rem; }
        .form-header h2 { font-size: 1.8rem; }
        .btn-premium { padding: 0.8rem; }
    }
</style>

<!-- FULLSCREEN CINEMATIC BACKGROUND -->
<div class="cinematic-bg">
    <div class="bg-shoe"></div>
    <div class="bg-overlay"></div>
    <div class="light-sweep"></div>
    <div class="red-pulse"></div>
    <div class="micro-grid"></div>
    <div class="vignette-cinema"></div>
    <div id="elegantParticles"></div>
</div>

<!-- LOGIN FORM -->
<div class="form-wrapper" id="form3dContainer">
    <div class="glass-card-clean" id="cleanGlassCard">
        <div class="form-header">
            <div class="logo-badge"><i class="fas fa-shoe-prints"></i></div>
            <h2>WELCOME BACK</h2>
            <p>enter the future of sneaker culture</p>
        </div>

        <form method="POST" action="{{ route('login') }}" id="premiumLoginForm">
            @csrf

            <div class="input-group-clean @error('email') error @enderror" id="emailGroupClean">
                <div class="floating-label-clean">
                    <input type="email" id="emailClean" name="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email" autofocus>
                    <label for="emailClean">Email address</label>
                </div>
                @error('email') <div class="error-message-clean">{{ $message }}</div> @enderror
                <div class="error-message-clean client-email-error" style="display: none;">Valid email required</div>
            </div>

            <div class="input-group-clean @error('password') error @enderror" id="passwordGroupClean">
                <div class="floating-label-clean">
                    <input type="password" id="passwordClean" name="password" placeholder=" " required autocomplete="current-password">
                    <label for="passwordClean">Password</label>
                    <button type="button" class="password-toggle-clean" id="togglePasswordClean"><i class="fas fa-eye-slash"></i></button>
                </div>
                @error('password') <div class="error-message-clean">{{ $message }}</div> @enderror
                <div class="error-message-clean client-password-error" style="display: none;">Password required</div>
            </div>

            <div class="form-options-clean">
                <label class="checkbox-clean">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Stay signed in</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="forgot-link-clean" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn-premium" id="premiumLoginBtn">
                <span>LOG IN →</span>
            </button>

            <div class="register-link-clean">
                New to the dimension? <a href="{{ route('register') }}">Register</a>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // ========== 1. ELEGANT PARTICLES (cinematic dust) ==========
        const particleField = document.getElementById('elegantParticles');
        if(particleField) {
            for(let i=0; i<140; i++) {
                const p = document.createElement('div');
                p.classList.add('particle-elegant');
                const size = Math.random() * 6 + 1.5;
                p.style.width = `${size}px`;
                p.style.height = `${size}px`;
                p.style.left = `${Math.random() * 100}%`;
                p.style.top = `${Math.random() * 100}%`;
                p.style.animationDuration = `${Math.random() * 18 + 10}s`;
                p.style.animationDelay = `${Math.random() * 10}s`;
                particleField.appendChild(p);
            }
        }

        // ========== 2. DEEP PARALLAX FOR BACKGROUND ==========
        const bgShoe = document.querySelector('.bg-shoe');
        if(bgShoe) {
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth) * 24 - 12;
                const y = (e.clientY / window.innerHeight) * 16 - 8;
                bgShoe.style.transform = `translate(${x * -0.6}px, ${y * -0.4}px) scale(1.12)`;
            });
        }

        // ========== 3. 3D TILT FOR FORM CARD (smooth & elegant) ==========
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

        // ========== 4. PASSWORD TOGGLE ==========
        const togglePass = document.getElementById('togglePasswordClean');
        const passInput = document.getElementById('passwordClean');
        if(togglePass && passInput) {
            togglePass.addEventListener('click', () => {
                const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passInput.setAttribute('type', type);
                const icon = togglePass.querySelector('i');
                icon.classList.toggle('fa-eye-slash');
                icon.classList.toggle('fa-eye');
            });
        }

        // ========== 5. RIPPLE ON BUTTON ==========
        const loginBtn = document.getElementById('premiumLoginBtn');
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
        if(loginBtn) {
            loginBtn.addEventListener('click', (e) => {
                if(!loginBtn.disabled) addRipple(e, loginBtn);
            });
        }

        // ========== 6. CLIENT VALIDATION ==========
        const form = document.getElementById('premiumLoginForm');
        const emailInput = document.getElementById('emailClean');
        const pwdInput = document.getElementById('passwordClean');
        const emailGroup = document.getElementById('emailGroupClean');
        const pwdGroup = document.getElementById('passwordGroupClean');
        const clientEmailErr = document.querySelector('.client-email-error');
        const clientPwdErr = document.querySelector('.client-password-error');

        function clearErrors() {
            if(emailGroup) emailGroup.classList.remove('error');
            if(pwdGroup) pwdGroup.classList.remove('error');
            if(clientEmailErr) clientEmailErr.style.display = 'none';
            if(clientPwdErr) clientPwdErr.style.display = 'none';
        }

        function validate() {
            let isValid = true;
            clearErrors();
            const emailVal = emailInput.value.trim();
            if(!emailVal || !/^\S+@\S+\.\S+$/.test(emailVal)) {
                if(emailGroup) emailGroup.classList.add('error');
                if(clientEmailErr) clientEmailErr.style.display = 'block';
                isValid = false;
            }
            if(!pwdInput.value) {
                if(pwdGroup) pwdGroup.classList.add('error');
                if(clientPwdErr) clientPwdErr.style.display = 'block';
                isValid = false;
            }
            return isValid;
        }

        if(form) {
            form.addEventListener('submit', function(e) {
                if(!validate()) {
                    e.preventDefault();
                    return;
                }
                loginBtn.disabled = true;
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i><span>AUTHENTICATING...</span>`;
            });
        }

        // show server errors on load
        if(document.querySelector('#emailGroupClean .error-message-clean:not(.client-email-error)')) {
            emailGroup?.classList.add('error');
        }
        if(document.querySelector('#passwordGroupClean .error-message-clean:not(.client-password-error)')) {
            pwdGroup?.classList.add('error');
        }
    })();
</script>
@endsection