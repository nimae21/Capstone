@extends('layouts.app')

@section('content')
<!-- ========================================================================
     ELEGANT CINEMATIC LOGIN – AIR JORDAN 1 CHICAGO BULLS
     No hover/tilt disruptions, smooth input glow, refined aesthetics.
     ======================================================================== -->
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<style>
  /* ---------- CINEMATIC BACKGROUND ANIMATIONS ---------- */
  @keyframes cinematicZoom {
    0% { transform: scale(1.08); filter: brightness(0.88) contrast(1.12); }
    100% { transform: scale(1.22); filter: brightness(0.92) contrast(1.18); }
  }
  .animate-cinematic-zoom {
    animation: cinematicZoom 22s infinite alternate ease-in-out;
  }

  @keyframes sweepGlide {
    0% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.2; }
    50% { transform: rotate(35deg) translateX(20%) translateY(20%); opacity: 0.5; }
    100% { transform: rotate(35deg) translateX(-40%) translateY(-40%); opacity: 0.2; }
  }
  .animate-sweep-glide {
    animation: sweepGlide 14s infinite cubic-bezier(0.45, 0.05, 0.2, 0.99);
  }

  @keyframes breatheRed {
    0% { opacity: 0.1; transform: scale(1); }
    100% { opacity: 0.4; transform: scale(1.1); }
  }
  .animate-breathe-red {
    animation: breatheRed 9s infinite alternate;
  }

  @keyframes floatSlow {
    0% { transform: translate(0,0) scale(1); opacity: 0.08; }
    100% { transform: translate(20px, -25px) scale(1.3); opacity: 0.25; }
  }
  .particle-elegant {
    position: absolute;
    background: rgba(230,0,35,0.25);
    border-radius: 50%;
    filter: blur(3px);
    pointer-events: none;
    animation: floatSlow 15s infinite alternate;
  }

  @keyframes gridDrift {
    0% { transform: translate(0,0); }
    100% { transform: translate(45px, 45px); }
  }
  .animate-grid-drift {
    animation: gridDrift 24s infinite linear;
  }

  @keyframes gentleFloat {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-6px); }
    100% { transform: translateY(0px); }
  }
  @keyframes entranceRise {
    from { opacity: 0; transform: translateY(30px) scale(0.96); filter: blur(4px); }
    to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
  }
  .glass-card-clean {
    animation: gentleFloat 5.5s infinite ease-in-out, entranceRise 0.8s ease-out 0.2s backwards;
    transition: box-shadow 0.3s ease;
  }
  /* Removed hover transform effects */

  @keyframes softGlow {
    0% { transform: scale(1); text-shadow: 0 0 0 rgba(230,0,35,0); }
    100% { transform: scale(1.03); text-shadow: 0 0 10px rgba(230,0,35,0.3); }
  }
  .animate-soft-glow {
    animation: softGlow 2.8s infinite alternate;
  }

  @keyframes gentleShake {
    0%,100% { transform: translateX(0); }
    20% { transform: translateX(-4px); }
    40% { transform: translateX(4px); }
    60% { transform: translateX(-2px); }
    80% { transform: translateX(2px); }
  }
  .animate-gentle-shake {
    animation: gentleShake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
  }

  @keyframes rippleClean {
    to { transform: scale(8); opacity: 0; }
  }
  .ripple-clean {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.6);
    transform: scale(0);
    animation: rippleClean 0.5s linear;
    pointer-events: none;
  }

  @keyframes lensFlare {
    0% { transform: translateX(-100%) rotate(25deg); opacity: 0; }
    20% { opacity: 0.4; }
    80% { opacity: 0.4; }
    100% { transform: translateX(200%) rotate(25deg); opacity: 0; }
  }
  .animate-lens-flare {
    animation: lensFlare 8s infinite ease-in-out;
  }

  @keyframes floatOrb {
    0% { transform: translate(0, 0) scale(1); opacity: 0.1; }
    50% { transform: translate(30px, -40px) scale(1.2); opacity: 0.3; }
    100% { transform: translate(-20px, 20px) scale(0.9); opacity: 0.1; }
  }
  .floating-orb {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(230,0,35,0.3), rgba(230,0,35,0));
    filter: blur(15px);
    pointer-events: none;
    animation: floatOrb 14s infinite alternate;
  }

  /* ---------- ELEGANT INPUT FIELDS (smooth focus + click glow) ---------- */
  .input-group-float {
    position: relative;
    margin-bottom: 1.5rem;
  }
  .input-group-float input {
    width: 100%;
    padding: 1rem 1rem 0.5rem 1rem;
    background-color: white;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 40px;
    font-size: 0.95rem;
    font-weight: 500;
    color: #1a1a1a;
    transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    outline: none;
  }
  /* Smooth focus glow - layered effect */
  .input-group-float input:focus {
    border-color: #E60023;
    box-shadow: 0 0 0 3px rgba(230,0,35,0.15), 0 0 0 6px rgba(230,0,35,0.05);
    transform: scale(1.01);
  }
  /* Additional click ripple effect (via JS) */
  .input-click-ripple {
    position: absolute;
    border-radius: 40px;
    background: radial-gradient(circle, rgba(230,0,35,0.2), transparent);
    pointer-events: none;
    transform: scale(0);
    transition: transform 0.4s ease-out, opacity 0.3s;
    opacity: 1;
  }
  .input-click-ripple.active {
    transform: scale(3);
    opacity: 0;
  }

  /* Floating label */
  .input-group-float label {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.9rem;
    color: #9a9fb0;
    pointer-events: none;
    transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    background: transparent;
    padding: 0 2px;
    font-weight: 500;
  }
  .input-group-float input:focus ~ label,
  .input-group-float input:not(:placeholder-shown) ~ label {
    top: 0.35rem;
    transform: translateY(0);
    font-size: 0.7rem;
    color: #E60023;
    font-weight: 700;
  }
  /* Animated underline spacer */
  .input-spacer {
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #E60023;
    transition: width 0.35s ease, left 0.35s ease;
    border-radius: 2px;
    pointer-events: none;
  }
  .input-group-float input:focus ~ .input-spacer {
    width: calc(100% - 2rem);
    left: 1rem;
  }

  /* Password toggle */
  .password-toggle {
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
    opacity: 0;
    pointer-events: none;
  }
  .password-toggle.visible {
    opacity: 1;
    pointer-events: auto;
  }
  .password-toggle:hover { color: #E60023; }

  /* Error styling */
  .input-group-float.error input {
    border-color: #E60023;
    background-color: #FFF8F8;
    animation: gentleShake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
  }
  .error-message {
    font-size: 0.68rem;
    color: #E60023;
    margin-top: 0.35rem;
    margin-left: 1rem;
    font-weight: 600;
  }

  /* Additional utilities */
  .backdrop-blur-16 {
    backdrop-filter: blur(16px);
  }
  .bg-gradient-radial {
    background-image: radial-gradient(circle, var(--tw-gradient-stops));
  }
  .animation-delay-2000 {
    animation-delay: 2s;
  }
  .animation-delay-5000 {
    animation-delay: 5s;
  }
</style>

<!-- BACKGROUND – scrolls with page, elegant cinematic layers -->
<body class="relative bg-black font-['Inter',sans-serif] overflow-x-hidden">
  <div class="absolute inset-0 z-0 overflow-hidden">
    <!-- Main shoe image with slow zoom -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=2000&auto=format')] bg-cover bg-center bg-no-repeat animate-cinematic-zoom" style="background-position: center 35%;"></div>
    
    <!-- Soft gradient overlays for elegance -->
    <div class="absolute inset-0 bg-gradient-radial from-black/30 via-black/50 to-black/70"></div>
    <div class="absolute -top-[30%] -left-[30%] w-[160%] h-[160%] bg-gradient-to-br from-[#E60023]/8 via-transparent to-transparent rotate-[35deg] animate-sweep-glide pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-radial from-[#E60023]/10 via-transparent to-transparent animate-breathe-red pointer-events-none" style="background-position: 40% 60%;"></div>
    
    <!-- Micro grid (very subtle) -->
    <div class="absolute inset-0 bg-[linear-gradient(rgba(230,0,35,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(230,0,35,0.015)_1px,transparent_1px)] bg-[length:45px_45px] pointer-events-none animate-grid-drift"></div>
    
    <!-- Vignette for depth -->
    <div class="absolute inset-0 bg-gradient-radial from-transparent via-transparent to-black/60 pointer-events-none"></div>
    
    <!-- Cinematic lens flare -->
    <div class="absolute top-1/4 left-0 w-[150%] h-[200%] bg-gradient-to-r from-transparent via-white/8 to-transparent rotate-[25deg] animate-lens-flare pointer-events-none"></div>
    
    <!-- Floating orbs (elegant, soft) -->
    <div class="floating-orb w-80 h-80 top-10 left-[10%]"></div>
    <div class="floating-orb w-96 h-96 bottom-20 right-[5%] animation-delay-2000"></div>
    <div class="floating-orb w-56 h-56 top-[40%] left-[80%] animation-delay-5000"></div>
    
    <!-- Particles container -->
    <div id="elegantParticles" class="absolute inset-0 pointer-events-none overflow-hidden"></div>
  </div>

  <!-- LOGIN FORM – stable, no hover/tilt effects -->
  <div class="relative z-20 min-h-screen flex items-center justify-center p-4 md:p-8">
    <div class="w-full max-w-[460px] bg-white/90 backdrop-blur-16 rounded-[48px] p-8 md:p-9 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.35),0_0_0_1px_rgba(255,255,255,0.7),0_0_0_2px_rgba(230,0,35,0.1),inset_0_1px_0_rgba(255,255,255,0.9)] transition-shadow duration-300 hover:shadow-[0_30px_60px_-15px_rgba(230,0,35,0.2),0_0_0_1px_rgba(255,255,255,0.8),0_0_0_2px_rgba(230,0,35,0.15),inset_0_1px_0_rgba(255,255,255,1)] glass-card-clean relative before:content-[''] before:absolute before:inset-0 before:rounded-[48px] before:bg-gradient-to-br before:from-white/30 before:to-transparent before:pointer-events-none" id="cleanGlassCard">
      
      <div class="text-center mb-7">
        <div class="text-5xl text-[#E60023] mb-2 animate-soft-glow inline-block">
          <i class="fas fa-shoe-prints"></i>
        </div>
        <h2 class="text-3xl md:text-4xl font-extrabold font-['Space_Grotesk',sans-serif] bg-gradient-to-r from-black via-black to-[#E60023] bg-clip-text text-transparent tracking-tight">
          WELCOME BACK KA WEAKNESS!
        </h2>
        <p class="text-[#5a5a6e] font-medium text-sm tracking-wide">Good to see you again! Log in to get started.</p>
      </div>

      <form method="POST" action="{{ route('login') }}" id="premiumLoginForm" class="space-y-5">
        @csrf

        <!-- Email Field -->
        <div class="input-group-float @error('email') error @enderror" id="emailGroupClean">
          <input type="email" id="emailClean" name="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email" autofocus>
          <label for="emailClean">Email address</label>
          <span class="input-spacer"></span>
          @error('email') <div class="error-message">{{ $message }}</div> @enderror
          <div class="error-message client-email-error hidden">Valid email required</div>
        </div>

        <!-- Password Field -->
        <div class="input-group-float @error('password') error @enderror" id="passwordGroupClean">
          <input type="password" id="passwordClean" name="password" placeholder=" " required autocomplete="current-password">
          <label for="passwordClean">Password</label>
          <span class="input-spacer"></span>
          <button type="button" class="password-toggle" id="togglePasswordClean">
            <i class="fas fa-eye-slash"></i>
          </button>
          @error('password') <div class="error-message">{{ $message }}</div> @enderror
          <div class="error-message client-password-error hidden">Password required</div>
        </div>

        <!-- Options Row -->
        <div class="flex justify-between items-center text-sm">
          <label class="flex items-center gap-2 cursor-pointer font-medium text-[#2e2e3e]">
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="accent-[#E60023] w-4 h-4">
            <span>Stay signed in</span>
          </label>
          @if (Route::has('password.request'))
            <a class="text-[#E60023] font-bold hover:underline transition-opacity" href="{{ route('password.request') }}">Forgot password?</a>
          @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-[#E60023] py-3.5 rounded-full font-extrabold text-sm text-white transition-all duration-200 hover:bg-[#C2001F] hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 shadow-[0_10px_22px_-10px_rgba(230,0,35,0.5)] relative overflow-hidden" id="premiumLoginBtn">
          <span>LOG IN →</span>
        </button>

        <!-- Register Link -->
        <div class="text-center pt-4 border-t border-black/5 text-sm font-medium">
          Don't have an account? <a href="{{ route('register') }}" class="text-[#E60023] font-extrabold hover:underline">Register</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function() {
      // ========== ELEGANT PARTICLES ==========
      const particleField = document.getElementById('elegantParticles');
      if(particleField) {
        for(let i=0; i<120; i++) { // slightly fewer for elegance
          const p = document.createElement('div');
          p.classList.add('particle-elegant');
          const size = Math.random() * 5 + 2;
          p.style.width = `${size}px`;
          p.style.height = `${size}px`;
          p.style.left = `${Math.random() * 100}%`;
          p.style.top = `${Math.random() * 100}%`;
          p.style.animationDuration = `${Math.random() * 18 + 12}s`;
          p.style.animationDelay = `${Math.random() * 10}s`;
          particleField.appendChild(p);
        }
      }

      // ========== SMOOTH PARALLAX (subtle, non-intrusive) ==========
      const bgShoe = document.querySelector('.animate-cinematic-zoom');
      if(bgShoe) {
        document.addEventListener('mousemove', (e) => {
          const x = (e.clientX / window.innerWidth) * 20 - 10;
          const y = (e.clientY / window.innerHeight) * 12 - 6;
          bgShoe.style.transform = `translate(${x * -0.4}px, ${y * -0.3}px) scale(1.12)`;
        });
      }

      // ========== INPUT CLICK RIPPLE EFFECT (smooth, elegant) ==========
      const inputs = document.querySelectorAll('.input-group-float input');
      inputs.forEach(input => {
        input.addEventListener('click', (e) => {
          // Create ripple element
          const ripple = document.createElement('div');
          ripple.className = 'input-click-ripple';
          const rect = input.getBoundingClientRect();
          ripple.style.position = 'absolute';
          ripple.style.top = '0';
          ripple.style.left = '0';
          ripple.style.width = '100%';
          ripple.style.height = '100%';
          ripple.style.borderRadius = '40px';
          ripple.style.background = 'radial-gradient(circle, rgba(230,0,35,0.2), transparent)';
          ripple.style.pointerEvents = 'none';
          ripple.style.transform = 'scale(0)';
          ripple.style.transition = 'transform 0.4s ease-out, opacity 0.3s';
          ripple.style.opacity = '1';
          input.parentElement.style.position = 'relative';
          input.parentElement.appendChild(ripple);
          // Trigger animation
          requestAnimationFrame(() => {
            ripple.style.transform = 'scale(2.5)';
            ripple.style.opacity = '0';
          });
          setTimeout(() => ripple.remove(), 400);
        });
      });

      // ========== PASSWORD TOGGLE – visible only when input has value ==========
      const toggleBtn = document.getElementById('togglePasswordClean');
      const passwordInput = document.getElementById('passwordClean');
      function updatePasswordToggle() {
        if(!toggleBtn || !passwordInput) return;
        const hasValue = passwordInput.value.trim().length > 0;
        if(hasValue) {
          toggleBtn.classList.add('visible');
        } else {
          toggleBtn.classList.remove('visible');
        }
      }
      if(toggleBtn && passwordInput) {
        updatePasswordToggle();
        passwordInput.addEventListener('input', updatePasswordToggle);
        toggleBtn.addEventListener('click', () => {
          const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordInput.setAttribute('type', type);
          const icon = toggleBtn.querySelector('i');
          icon.classList.toggle('fa-eye-slash');
          icon.classList.toggle('fa-eye');
        });
      }

      // ========== RIPPLE EFFECT ON LOGIN BUTTON ==========
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

      // ========== CLIENT VALIDATION ==========
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
        if(clientEmailErr) clientEmailErr.classList.add('hidden');
        if(clientPwdErr) clientPwdErr.classList.add('hidden');
      }
      function validate() {
        let isValid = true;
        clearErrors();
        const emailVal = emailInput.value.trim();
        if(!emailVal || !/^\S+@\S+\.\S+$/.test(emailVal)) {
          if(emailGroup) emailGroup.classList.add('error');
          if(clientEmailErr) clientEmailErr.classList.remove('hidden');
          isValid = false;
        }
        if(!pwdInput.value.trim()) {
          if(pwdGroup) pwdGroup.classList.add('error');
          if(clientPwdErr) clientPwdErr.classList.remove('hidden');
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
          loginBtn.classList.add('opacity-70', 'cursor-not-allowed');
          loginBtn.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i><span>AUTHENTICATING...</span>`;
        });
      }

      // Show server-side errors
      if(document.querySelector('#emailGroupClean .error-message:not(.client-email-error)')) {
        emailGroup?.classList.add('error');
      }
      if(document.querySelector('#passwordGroupClean .error-message:not(.client-password-error)')) {
        pwdGroup?.classList.add('error');
      }
    })();
  </script>
</body>
@endsection