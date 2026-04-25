@extends('layouts.admin')

@section('title', 'Edit Brand')
@section('page-title', 'Edit Brand')
@section('page-subtitle', 'Update brand information.')

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

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
        }

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

        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: transparent;
            color: #4b5563;
            font-weight: 600;
            padding: 0.7rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-outline:hover {
            background: #f9fafb;
            border-color: #dc2626;
            color: #dc2626;
            transform: translateY(-1px);
        }

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
    </style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto py-10 relative z-10">
    <div class="card-3d rounded-2xl p-6 md:p-8">
        <!-- Header -->
        <div class="flex items-center gap-2 mb-6">
            <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
            <h2 class="text-xl font-bold gradient-title">Edit Brand</h2>
        </div>

        <!-- Back Link -->
        <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-red-600 transition-all mb-6 hover:translate-x-[-2px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Brands
        </a>

        <!-- Form -->
        <form action="{{ route('admin.brands.update', $brand->brand_id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                <input type="text"
                       name="brand_name"
                       value="{{ old('brand_name', $brand->brand_name) }}"
                       class="input-premium"
                       placeholder="e.g., Nike, Adidas, Puma"
                       required>
                @error('brand_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('admin.brands.index') }}"
                   class="btn-outline">
                    Cancel
                </a>

                <button type="submit" class="btn-3d-red">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Update Brand
                </button>
            </div>
        </form>
    </div>

    <!-- Success Modal for Update -->
    @if(session('success'))
    <div id="updateSuccessModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-sm p-6 rounded-2xl text-center shadow-2xl" style="animation: modalFadeIn 0.3s ease-out;">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Updated!</h2>
            <p class="text-gray-600 mt-2">{{ session('success') }}</p>
            <button onclick="closeUpdateModal()" class="mt-4 px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-semibold hover:shadow-lg transition-all">OK</button>
        </div>
    </div>
    <style>
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
    <script>
        function closeUpdateModal() {
            document.getElementById('updateSuccessModal').style.display = 'none';
            window.location.href = "{{ route('admin.brands.index') }}";
        }
        setTimeout(() => {
            const modal = document.getElementById('updateSuccessModal');
            if (modal) modal.style.display = 'none';
        }, 3000);
    </script>
    @endif
</div>
@endsection