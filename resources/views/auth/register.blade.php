@extends('layouts.app')

@section('content')
<!-- ========================================================================
     CINEMATIC REGISTER – AIR JORDAN 1 CHICAGO BULLS
     Exact replica of login form styling & sizing (max-w-[460px])
     Middle Name & Suffix are optional
     ======================================================================== -->
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
  /* ---------- CINEMATIC BACKGROUND ANIMATIONS (same as login) ---------- */
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

  /* ---------- INPUT FIELDS (exactly as login) ---------- */
  .input-group-float {
    position: relative;
    margin-bottom: 1.5rem;
  }
  .input-group-float input {
    width: 100%;
    padding: 1rem 1rem 0.5rem 1rem;  /* = pt-5 pb-2 */
    background-color: white;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 40px;
    font-size: 0.95rem;
    font-weight: 500;
    color: #1a1a1a;
    transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    outline: none;
  }
  .input-group-float input:focus {
    border-color: #E60023;
    box-shadow: 0 0 0 3px rgba(230,0,35,0.15), 0 0 0 6px rgba(230,0,35,0.05);
    transform: scale(1.01);
  }
  .input-click-ripple {
    position: absolute;
    border-radius: 40px;
    background: radial-gradient(circle, rgba(230,0,35,0.2), transparent);
    pointer-events: none;
    transform: scale(0);
    transition: transform 0.4s ease-out, opacity 0.3s;
    opacity: 1;
  }
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

  /* Utilities */
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
  .logo-image {
    width: auto;
    height: 50px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}
</style>

<!-- BACKGROUND (identical to login) -->
<div class="relative bg-black font-['Inter',sans-serif] overflow-x-hidden">
  <div class="absolute inset-0 z-0 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=2000&auto=format')] bg-cover bg-center bg-no-repeat animate-cinematic-zoom" style="background-position: center 35%;"></div>
    <div class="absolute inset-0 bg-gradient-radial from-black/30 via-black/50 to-black/70"></div>
    <div class="absolute -top-[30%] -left-[30%] w-[160%] h-[160%] bg-gradient-to-br from-[#E60023]/8 via-transparent to-transparent rotate-[35deg] animate-sweep-glide pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-radial from-[#E60023]/10 via-transparent to-transparent animate-breathe-red pointer-events-none" style="background-position: 40% 60%;"></div>
    <div class="absolute inset-0 bg-[linear-gradient(rgba(230,0,35,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(230,0,35,0.015)_1px,transparent_1px)] bg-[length:45px_45px] pointer-events-none animate-grid-drift"></div>
    <div class="absolute inset-0 bg-gradient-radial from-transparent via-transparent to-black/60 pointer-events-none"></div>
    <div class="absolute top-1/4 left-0 w-[150%] h-[200%] bg-gradient-to-r from-transparent via-white/8 to-transparent rotate-[25deg] animate-lens-flare pointer-events-none"></div>
    <div class="floating-orb w-80 h-80 top-10 left-[10%]"></div>
    <div class="floating-orb w-96 h-96 bottom-20 right-[5%] animation-delay-2000"></div>
    <div class="floating-orb w-56 h-56 top-[40%] left-[80%] animation-delay-5000"></div>
    <div id="elegantParticles" class="absolute inset-0 pointer-events-none overflow-hidden"></div>
  </div>

  <!-- REGISTER FORM – same card width as login (max-w-[460px]) -->
  <div class="relative z-20 min-h-screen flex items-center justify-center p-4 md:p-8">
    <div class="w-full max-w-[460px] bg-white/90 backdrop-blur-16 rounded-[48px] p-8 md:p-9 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.35),0_0_0_1px_rgba(255,255,255,0.7),0_0_0_2px_rgba(230,0,35,0.1),inset_0_1px_0_rgba(255,255,255,0.9)] transition-shadow duration-300 hover:shadow-[0_30px_60px_-15px_rgba(230,0,35,0.2),0_0_0_1px_rgba(255,255,255,0.8),0_0_0_2px_rgba(230,0,35,0.15),inset_0_1px_0_rgba(255,255,255,1)] glass-card-clean relative before:content-[''] before:absolute before:inset-0 before:rounded-[48px] before:bg-gradient-to-br before:from-white/30 before:to-transparent before:pointer-events-none" id="cleanGlassCard">
      
      <div class="text-center mb-7">
        <img src="{{ asset('images/achilles logo foot.png') }}" alt="Achilles Logo" class="logo-image">
        <h2 class="text-3xl md:text-4xl font-extrabold font-['Space_Grotesk',sans-serif] bg-gradient-to-r from-black via-black to-[#E60023] bg-clip-text text-transparent tracking-tight">
          CREATE ACCOUNT
        </h2>
        <p class="text-[#5a5a6e] font-medium text-sm tracking-wide">join the movement – wear your weakness</p>
      </div>

      <form method="POST" action="{{ route('register') }}" id="premiumRegisterForm" class="space-y-5">
        @csrf

        <!-- Row 1: First Name (required) + Middle Name (optional) -->
        <div class="flex flex-col md:flex-row gap-4">
          <!-- First Name -->
          <div class="flex-1 input-group-float @error('first_name') error @enderror" id="firstNameGroup">
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder=" " required autofocus>
            <label for="first_name">First Name</label>
            <span class="input-spacer"></span>
            @error('first_name') <div class="error-message">{{ $message }}</div> @enderror
          </div>

          <!-- Middle Name (optional) -->
          <div class="flex-1 input-group-float @error('middle_name') error @enderror" id="middleNameGroup">
            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder=" ">
            <label for="middle_name">Middle Name <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
            <span class="input-spacer"></span>
            @error('middle_name') <div class="error-message">{{ $message }}</div> @enderror
          </div>
        </div>

        <!-- Row 2: Last Name (required) + Suffix (optional) -->
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Last Name -->
          <div class="flex-1 input-group-float @error('last_name') error @enderror" id="lastNameGroup">
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder=" " required>
            <label for="last_name">Last Name</label>
            <span class="input-spacer"></span>
            @error('last_name') <div class="error-message">{{ $message }}</div> @enderror
          </div>

          <!-- Suffix (optional) -->
          <div class="flex-1 input-group-float @error('suffix') error @enderror" id="suffixGroup">
            <input type="text" id="suffix" name="suffix" value="{{ old('suffix') }}" placeholder=" ">
            <label for="suffix">Suffix <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
            <span class="input-spacer"></span>
            @error('suffix') <div class="error-message">{{ $message }}</div> @enderror
          </div>
        </div>

        <!-- Email Field -->
        <div class="input-group-float @error('email') error @enderror" id="emailGroup">
          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" " required>
          <label for="email">Email Address</label>
          <span class="input-spacer"></span>
          @error('email') <div class="error-message">{{ $message }}</div> @enderror
        </div>

        <!-- Password Field -->
        <div class="input-group-float @error('password') error @enderror" id="passwordGroup">
          <input type="password" id="password" name="password" placeholder=" " required>
          <label for="password">Password</label>
          <span class="input-spacer"></span>
          <button type="button" class="password-toggle" id="togglePassword">
            <i class="fas fa-eye-slash"></i>
          </button>
          @error('password') <div class="error-message">{{ $message }}</div> @enderror
        </div>

        <!-- Confirm Password Field -->
        <div class="input-group-float" id="confirmPasswordGroup">
          <input type="password" id="password-confirm" name="password_confirmation" placeholder=" " required>
          <label for="password-confirm">Confirm Password</label>
          <span class="input-spacer"></span>
          <button type="button" class="password-toggle" id="toggleConfirmPassword">
            <i class="fas fa-eye-slash"></i>
          </button>
        </div>

        <!-- Terms & Conditions -->
<div class="mt-2">
    <label class="flex items-start gap-3 cursor-pointer text-sm text-[#4b4b5a]">
        <input
            type="checkbox"
            id="terms"
            name="terms"
            value="1"
            class="mt-1 accent-[#E60023] w-4 h-4 flex-shrink-0"
        >

        <span>
            I agree to the
            <button
                type="button"
                id="openTerms"
                class="text-[#E60023] font-extrabold hover:underline"
            >
                Terms & Conditions
            </button>
            of ACHILLES.
        </span>
    </label>

    <div
        id="termsError"
        class="error-message hidden"
        style="margin-left: 1.75rem;"
    >
        You must agree to the Terms & Conditions before registering.
    </div>
</div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-[#E60023] py-3.5 rounded-full font-extrabold text-sm text-white transition-all duration-200 hover:bg-[#C2001F] hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2 shadow-[0_10px_22px_-10px_rgba(230,0,35,0.5)] relative overflow-hidden" id="premiumRegisterBtn">
          <span>REGISTER →</span>
        </button>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-black/5 text-sm font-medium">
          Already have an account? <a href="{{ route('login') }}" class="text-[#E60023] font-extrabold hover:underline">Login</a>
        </div>
      </form>

      <!-- =========================================================
     TERMS & CONDITIONS MODAL
     ========================================================= -->
<div
    id="termsModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4"
    aria-hidden="true"
>
    <!-- Backdrop -->
    <div
        id="termsBackdrop"
        class="absolute inset-0 bg-black/70 backdrop-blur-sm"
    ></div>

    <!-- Modal -->
    <div
        id="termsDialog"
        class="relative z-10 w-full max-w-3xl max-h-[90vh] bg-white rounded-[28px] shadow-[0_30px_80px_-20px_rgba(0,0,0,0.55)] overflow-hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="termsTitle"
    >

        <!-- Header -->
        <div class="flex items-center justify-between px-6 md:px-8 py-5 border-b border-black/5 bg-white">

            <div>
                <p class="text-[0.65rem] uppercase tracking-[0.15em] font-extrabold text-[#E60023]">
                    ACHILLES
                </p>

                <h2
                    id="termsTitle"
                    class="text-2xl md:text-3xl font-extrabold text-[#111827]"
                >
                    Terms & Conditions
                </h2>

                <p class="text-xs text-gray-400 mt-1">
                    Last updated: September 7, 2026
                </p>
            </div>

            <button
                type="button"
                id="closeTerms"
                class="w-10 h-10 rounded-full bg-gray-100 hover:bg-red-50 hover:text-[#E60023] flex items-center justify-center transition"
                aria-label="Close Terms and Conditions"
            >
                <i class="fas fa-times"></i>
            </button>

        </div>

        <!-- Scrollable Content -->
        <div
            id="termsContent"
            class="px-6 md:px-8 py-6 overflow-y-auto max-h-[65vh] text-sm leading-7 text-[#4b5563]"
        >

            <p class="mb-5">
                Welcome to ACHILLES. These Terms & Conditions govern your
                access to and use of the ACHILLES website, online store,
                products, services, and related features. By creating an
                account or placing an order, you agree to comply with these
                Terms & Conditions.
            </p>

            <!-- 1 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    1. About ACHILLES
                </h3>

                <p>
                    ACHILLES is an online shoe retail platform that allows
                    customers to browse footwear products, create an account,
                    add products to a cart, and place orders through the
                    platform.
                </p>
            </section>

            <!-- 2 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    2. Account Registration
                </h3>

                <p class="mb-2">
                    To use certain features of ACHILLES, you may be required
                    to create an account. You agree to provide information
                    that is accurate, complete, and up to date.
                </p>

                <p>
                    You are responsible for maintaining the confidentiality
                    of your account credentials and for activities performed
                    through your account. You should notify ACHILLES if you
                    believe your account has been accessed without your
                    authorization.
                </p>
            </section>

            <!-- 3 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    3. Products, Images, and Information
                </h3>

                <p class="mb-2">
                    ACHILLES makes reasonable efforts to ensure that product
                    names, descriptions, photographs, sizes, colors, prices,
                    and availability displayed on the platform are accurate.
                </p>

                <p>
                    However, colors may appear differently depending on your
                    device or display settings. Product availability may also
                    change before an order is confirmed.
                </p>
            </section>

            <!-- 4 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    4. Prices
                </h3>

                <p>
                    Product prices are displayed in Philippine Pesos (₱)
                    unless otherwise stated. ACHILLES reserves the right to
                    update prices, promotions, and product information at any
                    time. Changes will not affect an order that has already
                    been properly confirmed, except where correction is
                    required because of an obvious pricing or system error.
                </p>
            </section>

            <!-- 5 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    5. Orders and Order Confirmation
                </h3>

                <p class="mb-2">
                    Adding an item to your cart does not guarantee that the
                    product will remain available or that an order has been
                    accepted.
                </p>

                <p>
                    An order is subject to product availability, successful
                    payment or payment verification where applicable, and
                    other applicable order requirements. ACHILLES may cancel
                    or decline an order when necessary, including in cases
                    involving unavailable inventory, obvious pricing errors,
                    suspected fraudulent activity, or technical errors.
                </p>
            </section>

            <!-- 6 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    6. Payment
                </h3>

                <p>
                    Customers are responsible for providing accurate payment
                    information and completing payment through the payment
                    methods made available by ACHILLES. Payment processing
                    may be handled by third-party payment service providers.
                    ACHILLES does not request or store your full payment card
                    credentials through ordinary account registration.
                </p>
            </section>

            <!-- 7 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    7. Shipping and Delivery
                </h3>

                <p class="mb-2">
                    Delivery times may vary depending on the customer's
                    location, courier availability, weather, holidays,
                    operational conditions, and other circumstances beyond
                    ACHILLES' reasonable control.
                </p>

                <p>
                    Customers are responsible for providing a complete and
                    accurate delivery address and contact information.
                    ACHILLES is not responsible for delays caused by
                    incorrect or incomplete information supplied by the
                    customer.
                </p>
            </section>

            <!-- 8 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    8. Cancellations, Returns, and Refunds
                </h3>

                <p class="mb-2">
                    Cancellation, return, replacement, and refund requests
                    are subject to ACHILLES' applicable store policies and
                    the rights provided to consumers under Philippine law.
                </p>

                <p class="mb-2">
                    Products that are defective, damaged, incorrect, or
                    otherwise covered by applicable consumer protection
                    requirements may qualify for an appropriate remedy,
                    subject to verification and the applicable conditions.
                </p>

                <p>
                    Customers may be required to provide order information,
                    photographs, or other reasonable evidence when reporting
                    a damaged, defective, or incorrect item.
                </p>
            </section>

            <!-- 9 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    9. Customer Responsibilities
                </h3>

                <p>
                    Customers agree not to use ACHILLES for unlawful
                    activities, fraudulent transactions, unauthorized access,
                    abuse of promotions, interference with the platform, or
                    activities intended to disrupt or compromise the security
                    of the service.
                </p>
            </section>

            <!-- 10 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    10. Intellectual Property
                </h3>

                <p>
                    Unless otherwise stated, ACHILLES' website design,
                    branding, logos, text, graphics, product presentation,
                    software, and other original content are owned by or
                    licensed to ACHILLES and may not be copied, reproduced,
                    modified, distributed, or commercially exploited without
                    appropriate authorization.
                </p>
            </section>

            <!-- 11 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    11. Privacy and Personal Information
                </h3>

                <p>
                    ACHILLES may collect and process personal information
                    necessary to operate the account, process orders,
                    communicate with customers, provide customer support,
                    and perform other legitimate business functions.
                </p>

                <p class="mt-2">
                    Personal information will be handled in accordance with
                    applicable Philippine data privacy laws and ACHILLES'
                    Privacy Policy.
                </p>
            </section>

            <!-- 12 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    12. Third-Party Services
                </h3>

                <p>
                    ACHILLES may use third-party services such as payment
                    processors, delivery providers, analytics services,
                    hosting providers, or other technology providers.
                    Their services may be governed by their own terms and
                    privacy policies.
                </p>
            </section>

            <!-- 13 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    13. Availability and Technical Issues
                </h3>

                <p>
                    ACHILLES aims to keep the platform available and
                    functional but does not guarantee uninterrupted access.
                    Temporary interruptions may occur because of maintenance,
                    technical failures, network problems, third-party
                    services, or circumstances beyond reasonable control.
                </p>
            </section>

            <!-- 14 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    14. Limitation of Responsibility
                </h3>

                <p>
                    ACHILLES will take reasonable measures to operate the
                    platform and fulfill confirmed orders. Nothing in these
                    Terms & Conditions is intended to remove, restrict, or
                    waive any consumer right or legal protection that cannot
                    lawfully be excluded under applicable Philippine law.
                </p>
            </section>

            <!-- 15 -->
            <section class="mb-6">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    15. Changes to These Terms
                </h3>

                <p>
                    ACHILLES may update these Terms & Conditions when
                    necessary to reflect changes in the platform, services,
                    business practices, or applicable legal requirements.
                    Updated terms will be posted on the platform with a
                    revised effective or update date.
                </p>
            </section>

            <!-- 16 -->
            <section class="mb-2">
                <h3 class="text-base font-extrabold text-[#111827] mb-2">
                    16. Contact and Customer Concerns
                </h3>

                <p>
                    If you have questions regarding an order, product,
                    cancellation, return, refund, account, or these Terms &
                    Conditions, please contact ACHILLES through the customer
                    support channels provided on the platform.
                </p>
            </section>

            <div class="mt-8 p-4 rounded-2xl bg-red-50 border border-red-100">
                <p class="text-xs text-gray-600 leading-6">
                    By registering for an ACHILLES account, you acknowledge
                    that you have read and understood these Terms &
                    Conditions and agree to be bound by them, subject to
                    applicable Philippine law and your rights as a consumer.
                </p>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 md:px-8 py-4 border-t border-black/5 bg-gray-50 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">

            <span class="text-xs text-gray-400">
                Please review the terms before registering.
            </span>

            <button
                type="button"
                id="acceptTerms"
                class="bg-[#E60023] text-white px-6 py-2.5 rounded-full text-sm font-extrabold hover:bg-[#C2001F] transition"
            >
                I Understand
            </button>

        </div>

    </div>
</div>
    </div>
  </div>

  <script>
(function () {

    // =========================
    // PARTICLES
    // =========================
    const particleField = document.getElementById('elegantParticles');

    if (particleField && window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)').matches) {
        for (let i = 0; i < 120; i++) {
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

    // =========================
    // PARALLAX
    // =========================
    const bgShoe = document.querySelector('.animate-cinematic-zoom');

    if (bgShoe && window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)').matches) {
        document.addEventListener('mousemove', (e) => {

            const x = (e.clientX / window.innerWidth) * 20 - 10;
            const y = (e.clientY / window.innerHeight) * 12 - 6;

            bgShoe.style.transform =
                `translate(${x * -0.4}px, ${y * -0.3}px) scale(1.12)`;
        });
    }

    // =========================
    // INPUT RIPPLE
    // =========================
    const inputs = document.querySelectorAll('.input-group-float input');

    inputs.forEach(input => {

        input.addEventListener('click', () => {

            const ripple = document.createElement('div');

            ripple.className = 'input-click-ripple';

            ripple.style.position = 'absolute';
            ripple.style.top = '0';
            ripple.style.left = '0';
            ripple.style.width = '100%';
            ripple.style.height = '100%';
            ripple.style.borderRadius = '40px';
            ripple.style.background =
                'radial-gradient(circle, rgba(230,0,35,0.2), transparent)';
            ripple.style.pointerEvents = 'none';
            ripple.style.transform = 'scale(0)';
            ripple.style.transition =
                'transform 0.4s ease-out, opacity 0.3s';

            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(ripple);

            requestAnimationFrame(() => {
                ripple.style.transform = 'scale(2.5)';
                ripple.style.opacity = '0';
            });

            setTimeout(() => ripple.remove(), 400);
        });
    });

    // =========================
    // PASSWORD TOGGLE
    // =========================
    function initPasswordToggle(toggleBtn, passwordInput) {

        if (!toggleBtn || !passwordInput) return;

        function updateVisibility() {

            const hasValue = passwordInput.value.trim().length > 0;

            if (hasValue) {
                toggleBtn.classList.add('visible');
            } else {
                toggleBtn.classList.remove('visible');
            }
        }

        updateVisibility();

        passwordInput.addEventListener('input', updateVisibility);

        toggleBtn.addEventListener('click', () => {

            const type =
                passwordInput.getAttribute('type') === 'password'
                    ? 'text'
                    : 'password';

            passwordInput.setAttribute('type', type);

            const icon = toggleBtn.querySelector('i');

            icon.classList.toggle('fa-eye-slash');
            icon.classList.toggle('fa-eye');
        });
    }

    initPasswordToggle(
        document.getElementById('togglePassword'),
        document.getElementById('password')
    );

    initPasswordToggle(
        document.getElementById('toggleConfirmPassword'),
        document.getElementById('password-confirm')
    );

    // =========================
    // BUTTON RIPPLE
    // =========================
    const registerBtn =
        document.getElementById('premiumRegisterBtn');

    function addRipple(e, btn) {

        const rect = btn.getBoundingClientRect();

        const size = Math.max(rect.width, rect.height);

        const ripple = document.createElement('span');

        ripple.className = 'ripple-clean';

        ripple.style.width = ripple.style.height = `${size}px`;

        ripple.style.left =
            `${e.clientX - rect.left - size / 2}px`;

        ripple.style.top =
            `${e.clientY - rect.top - size / 2}px`;

        btn.appendChild(ripple);

        setTimeout(() => ripple.remove(), 500);
    }

    if (registerBtn) {
        registerBtn.addEventListener('click', (e) => {

            if (!registerBtn.disabled) {
                addRipple(e, registerBtn);
            }
        });
    }

    // =========================
    // ELEMENT REFERENCES (declared once, used by validation + modal below)
    // =========================
    const termsModal = document.getElementById('termsModal');
    const openTerms = document.getElementById('openTerms');
    const closeTerms = document.getElementById('closeTerms');
    const termsBackdrop = document.getElementById('termsBackdrop');
    const acceptTerms = document.getElementById('acceptTerms');
    const termsCheckbox = document.getElementById('terms');
    const termsError = document.getElementById('termsError');

    // =========================
    // FORM VALIDATION
    // =========================
    const form =
        document.getElementById('premiumRegisterForm');

    const emailInput =
        document.getElementById('email');

    const pwdInput =
        document.getElementById('password');

    const confirmPwdInput =
        document.getElementById('password-confirm');

    const emailGroup =
        document.getElementById('emailGroup');

    const passwordGroup =
        document.getElementById('passwordGroup');

    const confirmGroup =
        document.getElementById('confirmPasswordGroup');

    function clearErrors() {

        document.querySelectorAll('.custom-error')
            .forEach(el => el.remove());

        emailGroup?.classList.remove('error');
        passwordGroup?.classList.remove('error');
        confirmGroup?.classList.remove('error');
    }

    function createError(group, message) {

        group.classList.add('error');

        const err = document.createElement('div');

        err.className = 'error-message custom-error';

        err.textContent = message;

        group.appendChild(err);
    }

    function validate() {

    let isValid = true;

    clearErrors();

    // =========================
    // EMAIL
    // =========================
    const emailVal = emailInput.value.trim();

    if (!emailVal || !/^\S+@\S+\.\S+$/.test(emailVal)) {

        createError(
            emailGroup,
            'Valid email required'
        );

        isValid = false;
    }

    // =========================
    // TERMS & CONDITIONS
    // =========================
    if (!termsCheckbox || !termsCheckbox.checked) {

        if (termsError) {
            termsError.classList.remove('hidden');
        }

        isValid = false;

    } else {

        if (termsError) {
            termsError.classList.add('hidden');
        }
    }

    // =========================
    // PASSWORD STRENGTH
    // =========================
    const password = pwdInput.value;

    const strongPassword =
        /[a-z]/.test(password) &&
        /[A-Z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[^A-Za-z0-9]/.test(password) &&
        password.length >= 8;

    if (!strongPassword) {

        createError(
            passwordGroup,
            'Password must contain uppercase, lowercase, number, symbol, and 8+ chars'
        );

        isValid = false;
    }

    // =========================
    // PASSWORD MATCH
    // =========================
    if (pwdInput.value !== confirmPwdInput.value) {

        createError(
            confirmGroup,
            'Passwords do not match'
        );

        isValid = false;
    }

    return isValid;
}

    // =========================
    // FORM SUBMIT
    // =========================
    if (form) {

        form.addEventListener('submit', function (e) {

            if (!validate()) {

                e.preventDefault();

                return;
            }

            registerBtn.disabled = true;

            registerBtn.classList.add(
                'opacity-70',
                'cursor-not-allowed'
            );

            registerBtn.innerHTML =
                `<i class="fas fa-circle-notch fa-spin"></i>
                 <span>CREATING ACCOUNT...</span>`;
        });
    }

    // =========================================================
    // TERMS & CONDITIONS MODAL
    // =========================================================

    function showTermsModal() {
        if (!termsModal) return;

        termsModal.classList.remove('hidden');
        termsModal.classList.add('flex');
        termsModal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }

    function hideTermsModal() {
        if (!termsModal) return;

        termsModal.classList.add('hidden');
        termsModal.classList.remove('flex');
        termsModal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('overflow-hidden');
    }

    if (openTerms) {
        openTerms.addEventListener('click', function () {
            showTermsModal();
        });
    }

    if (closeTerms) {
        closeTerms.addEventListener('click', function () {
            hideTermsModal();
        });
    }

    if (termsBackdrop) {
        termsBackdrop.addEventListener('click', function () {
            hideTermsModal();
        });
    }

    if (acceptTerms) {
        acceptTerms.addEventListener('click', function () {

            if (termsCheckbox) {
                termsCheckbox.checked = true;
            }

            if (termsError) {
                termsError.classList.add('hidden');
            }

            hideTermsModal();
        });
    }

    // ESC key closes modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && termsModal && !termsModal.classList.contains('hidden')) {
            hideTermsModal();
        }
    });

})();
</script>
</div>
@endsection