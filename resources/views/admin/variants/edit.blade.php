@extends('layouts.admin')

@section('title', 'Edit Variant')
@section('page-title', 'Edit Variant')
@section('page-subtitle', 'Update size and color for this variant.')

@section('styles')
    <!-- Tailwind CSS + Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
            position: relative;
        }

        /* Cinematic animated gradient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(220,38,38,0.04) 0%, rgba(0,0,0,0.02) 50%, transparent 80%);
            animation: cinematicMove 20s ease infinite;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes cinematicMove {
            0% { transform: translate(0, 0); }
            50% { transform: translate(5%, 5%); }
            100% { transform: translate(0, 0); }
        }

        /* 3D Card effect - premium glassmorphism */
        .card-3d {
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05), 0 1px 1px rgba(0,0,0,0.02);
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .card-3d:hover {
            transform: translateY(-6px) translateZ(12px);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15);
            border-color: rgba(220,38,38,0.2);
        }

        /* Bold gradient titles - Black & Red */
        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
        }

        /* 3D Button - Red (Update Variant) */
        .btn-3d-red {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.08s linear;
            box-shadow: 0 8px 0 #991b1b, 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .btn-3d-red:active {
            transform: translateY(6px);
            box-shadow: 0 2px 0 #991b1b;
        }
        .btn-3d-red:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        /* Premium input fields */
        .input-premium {
            transition: all 0.2s ease;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.625rem 1rem;
            width: 100%;
        }
        .input-premium:focus {
            transform: translateY(-1px);
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
            outline: none;
        }

        /* Back link */
        .back-link {
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
        }
        .back-link:hover {
            transform: translateX(-4px);
            color: #dc2626;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative z-10">
        <!-- Back to Manage Variants Link -->
        <div class="mb-6">
            <a href="{{ route('admin.products.variants.index', $variant->product_id) }}" class="back-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Variants
            </a>
        </div>

        <!-- Edit Variant Form Card -->
        <div class="card-3d rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <h3 class="text-2xl font-bold gradient-title">Edit Variant</h3>
            </div>

            <form action="{{ route('admin.variants.update', $variant->product_variant_id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="size" class="block text-sm font-semibold text-gray-700 mb-1">Size <span class="text-red-500">*</span></label>
                    <input type="text" name="size" id="size" value="{{ old('size', $variant->size) }}" required
                           class="input-premium" placeholder="e.g., 42, M, XL, 10.5">
                </div>

                <div>
                    <label for="color" class="block text-sm font-semibold text-gray-700 mb-1">Color <span class="text-red-500">*</span></label>
                    <input type="text" name="color" id="color" value="{{ old('color', $variant->color) }}" required
                           class="input-premium" placeholder="e.g., Black, Red, White">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-3d-red w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Update Variant
                    </button>
                </div>
            </form>
        </div>

        <!-- Optional subtle footer tip -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">Changing size/color will affect stock entries for this variant.</p>
        </div>
    </div>
@endsection