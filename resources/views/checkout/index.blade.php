<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Checkout | ACHILLES</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background-color: #ffffff; overflow-x: hidden; }
        
        @keyframes subtleFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(1deg); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .float-orb { animation: subtleFloat 12s ease-in-out infinite; }
        .float-orb-delayed { animation: subtleFloat 15s ease-in-out infinite reverse; }
        
        .glass-nav {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(16px);
        }
        
        .hero-grid-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,0.02) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        }
        
        /* 3D Button Styles */
        .btn-3d {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            font-weight: 600;
            border-radius: 12px;
            padding: 14px 32px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 0 0 #7f1d1d, 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            cursor: pointer;
            border: none;
            width: 100%;
        }
        
        .btn-3d:hover {
            transform: translateY(0px);
            box-shadow: 0 1px 0 0 #7f1d1d, 0 4px 12px rgba(220, 38, 38, 0.4);
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .btn-3d:active {
            transform: translateY(2px);
            box-shadow: 0 0px 0 0 #7f1d1d;
        }
        
        .input-field {
            transition: all 0.2s ease;
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background-color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .input-field:hover {
            border-color: #d1d5db;
        }
        
        .error-message {
            animation: fadeInUp 0.3s ease-out forwards;
        }
    </style>
</head>
<body class="antialiased">

    <!-- CINEMATIC BACKGROUND ORBS + LIGHTING -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] bg-red-50/40 rounded-full blur-[100px] float-orb"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[550px] h-[550px] bg-gray-100/60 rounded-full blur-[120px] float-orb-delayed"></div>
        <div class="absolute top-[30%] right-[20%] w-[300px] h-[300px] bg-red-100/30 rounded-full blur-[80px]"></div>
        <div class="hero-grid-pattern absolute inset-0 opacity-40"></div>
        <div class="absolute top-0 left-0 w-full h-[500px] bg-gradient-to-b from-white via-white/95 to-transparent pointer-events-none"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-5 lg:px-8 py-6">
        
        <!-- STICKY NAVBAR -->
        <div class="sticky top-4 z-50 rounded-2xl glass-nav border border-white/40 shadow-sm mb-8 transition-all duration-300">
            <div class="flex items-center py-3 px-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-400 rounded-xl shadow-md flex items-center justify-center">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">ACHILLES</span>
                </div>
            </div>
        </div>

        <!-- CHECKOUT HEADER -->
        <div class="fade-in-up mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6.5M17 13l1.5 6.5M9 21h6M12 17v4"></path></svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gray-900">Checkout</h1>
            </div>
            <div class="w-20 h-0.5 bg-red-500/70 rounded-full mt-2"></div>
            <p class="text-gray-500 mt-3 text-sm">Complete your purchase with our secure checkout process</p>
        </div>

        <!-- ERROR MESSAGES -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl error-message">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl error-message">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="text-red-700 font-medium mb-1">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- CHECKOUT FORM -->
        <form action="{{ route('checkout.place') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- LEFT COLUMN - BILLING INFORMATION -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Billing Information</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">Full Name</span>
                                    <input type="text" name="full_name" placeholder="Enter your full name" value="{{ old('full_name') }}" required class="input-field">
                                </label>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">Street Address</span>
                                    <input type="text" name="street" placeholder="House number and street name" value="{{ old('street') }}" required class="input-field">
                                </label>
                            </div>
                            
                            <div>
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">Barangay</span>
                                    <input type="text" name="barangay" placeholder="Barangay" value="{{ old('barangay') }}" required class="input-field">
                                </label>
                            </div>
                            
                            <div>
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">City</span>
                                    <input type="text" name="city" placeholder="City" value="{{ old('city') }}" required class="input-field">
                                </label>
                            </div>
                            
                            <div>
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">Province</span>
                                    <input type="text" name="province" placeholder="Province" value="{{ old('province') }}" required class="input-field">
                                </label>
                            </div>
                            
                            <div>
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">Postal Code</span>
                                    <input type="text" name="postal_code" placeholder="Postal code" value="{{ old('postal_code') }}" required class="input-field">
                                </label>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-2 block">Phone Number</span>
                                    <input type="text" name="phone_number" placeholder="Contact number" value="{{ old('phone_number') }}" required class="input-field">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN - ORDER SUMMARY -->
                <div>
                    <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-100 rounded-3xl p-6 shadow-sm sticky top-24">
                        <div class="flex items-center gap-2 mb-5">
                            <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Order Summary</h2>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Subtotal</span>
                                <span class="text-sm font-semibold text-gray-900">To be calculated</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Shipping</span>
                                <span class="text-sm font-semibold text-gray-900">150-200</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 mt-2">
                                <span class="text-base font-bold text-gray-900">Total</span>
                                <span class="text-2xl font-black text-red-600">Will update</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-3d">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Place Order
                        </button>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-center gap-2 text-xs text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <span>Secure checkout • SSL Encrypted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="h-12"></div>
    </div>

</body>
</html>