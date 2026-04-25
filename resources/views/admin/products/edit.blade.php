@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')
@section('page-subtitle', 'Update product details here.')

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

        /* 3D Card effect */
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

        /* 3D Button - Red Update */
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

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            border-radius: 10px;
        }
    </style>
@endsection
@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative z-10">
        <!-- Back to Products Link -->
        <div class="mb-6">
            <a href="{{ route('admin.products.index') }}" class="back-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Products
            </a>
        </div>

        <!-- Current Product Images -->
<div class="mb-6">
    <label class="block text-sm font-semibold text-gray-700 mb-2">
        Current Images
    </label>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @forelse($product->images as $image)
            <div class="relative">
                <img src="{{ asset($image->image_path) }}"
                     class="w-full h-28 object-cover rounded-xl border">

                @if($image->is_primary)
                    <span class="absolute top-2 left-2 text-xs bg-red-500 text-white px-2 py-1 rounded">
                        Primary
                    </span>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400">No images available.</p>
        @endforelse
    </div>
</div>

        <!-- Edit Product Form Card -->
        <div class="card-3d rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <h3 class="text-2xl font-bold gradient-title">Edit Product</h3>
            </div>

            <form action="{{ route('admin.products.update', $product->product_id) }}"
      method="POST" 
      enctype="multipart/form-data"
      class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Product Name -->
                <div>
                    <label for="product_name" class="block text-sm font-semibold text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="product_name" id="product_name" value="{{ old('product_name', $product->product_name) }}" required
                           class="input-premium">
                </div>

                <!-- Description -->
                <div>
                    <label for="product_description" class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="product_description" id="product_description" rows="4"
                           class="input-premium resize-none">{{ old('product_description', $product->product_description) }}</textarea>
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required class="input-premium">
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}" {{ (old('category_id', $product->category_id) == $category->category_id) ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand_id" class="block text-sm font-semibold text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
                    <select name="brand_id" id="brand_id" required class="input-premium">
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_id }}" {{ (old('brand_id', $product->brand_id) == $brand->brand_id) ? 'selected' : '' }}>
                                {{ $brand->brand_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Upload New Images -->
<div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">
        Add New Images
    </label>

    <input type="file"
           name="images[]"
           multiple
           class="input-premium">
</div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="btn-3d-red w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Update Product
                    </button>
                </div>
            </form>
        </div>

        <!-- Optional: Small elegant tip (like product management hint) -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">Keep your product catalog organised with clear names and categories.</p>
        </div>
    </div>
@endsection

@if(session('success') || session('error') || $errors->any())
<div id="systemModal"
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">

        @if(session('success'))
            <h2 class="text-green-600 font-bold text-lg mb-2">Success</h2>
            <p class="text-gray-700">{{ session('success') }}</p>
        @endif

        @if(session('error'))
            <h2 class="text-red-600 font-bold text-lg mb-2">Error</h2>
            <p class="text-gray-700">{{ session('error') }}</p>
        @endif

        @if($errors->any())
            <h2 class="text-yellow-600 font-bold text-lg mb-2">Validation Error</h2>
            <ul class="text-sm text-gray-700 list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button onclick="document.getElementById('systemModal').remove()"
                class="mt-4 w-full bg-black text-white py-2 rounded-lg">
            Close
        </button>
    </div>
</div>
@endif