@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products Management')
@section('page-subtitle', 'Manage your products, variants, and stock levels.')

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

        /* 3D Card effect with cinematic lift */
        .card-cinematic {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05), 0 1px 1px rgba(0,0,0,0.02);
        }
        .card-cinematic:hover {
            transform: translateY(-6px) translateZ(12px);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15);
            border-color: rgba(220,38,38,0.2);
        }

        /* Premium gradient tips card (black/red accents) */
        .tips-premium-gradient {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,250,252,0.98));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        .tips-premium-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 150%;
            height: 200%;
            background: radial-gradient(circle, rgba(220,38,38,0.06) 0%, transparent 70%);
            pointer-events: none;
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

        /* 3D Red Create Button */
        .btn-create-3d {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            box-shadow: 0 8px 0 #991b1b, 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            transition: all 0.08s linear;
            font-weight: 600;
            border-radius: 0.75rem;
        }
        .btn-create-3d:active {
            transform: translateY(6px);
            box-shadow: 0 2px 0 #991b1b;
        }
        .btn-create-3d:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        /* Small action buttons 3D */
        .btn-sm-3d {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.05s linear;
            transform: translateY(-1px);
            text-decoration: none;
        }
        .btn-sm-3d:active { transform: translateY(3px); }
        .btn-sm-blue {
            background: #3b82f6;
            color: white;
            border: 1px solid #2563eb;
            box-shadow: 0 3px 0 #1e40af;
        }
        .btn-sm-blue:active { box-shadow: 0 0px 0 #1e40af; }
        .btn-sm-blue:hover { background: #2563eb; }
        .btn-sm-red {
            background: #ef4444;
            color: white;
            border: 1px solid #dc2626;
            box-shadow: 0 3px 0 #991b1b;
        }
        .btn-sm-red:active { box-shadow: 0 0px 0 #991b1b; }
        .btn-sm-red:hover { background: #dc2626; }
        .btn-sm-green {
            background: #10b981;
            color: white;
            border: 1px solid #059669;
            box-shadow: 0 3px 0 #047857;
        }
        .btn-sm-green:active { box-shadow: 0 0px 0 #047857; }
        .btn-sm-green:hover { background: #059669; }

        /* 3D Table Row */
        .table-row-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .table-row-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(6px);
            background: linear-gradient(90deg, #ffffff, #fafcff);
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
        }

        /* Product card - strongly isolated */
        .product-card {
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            border: 1px solid #f0f0f0;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
        }
        /* Strong visual separator between product cards */
        .product-card:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: -1rem;
            left: 10%;
            width: 80%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #dc2626, #000000, #dc2626, transparent);
            border-radius: 2px;
        }
        .product-card:hover {
            transform: translateY(-3px) translateZ(6px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.15);
            border-color: #dc2626;
        }

        /* Compact form inputs */
        .input-compact {
            transition: all 0.2s;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            width: 100%;
        }
        .input-compact:focus {
            transform: translateY(-1px);
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
            outline: none;
        }

        /* Stats card premium */
        .stat-card {
            background: linear-gradient(115deg, #ffffff, #fefefe);
            border-radius: 1rem;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1);
        }

        /* Create product form container – centered horizontally */
        .create-product-container {
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Scrollbar */
        .custom-scroll::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 relative z-10">
        
        <!-- Success Alert -->
        @if(session('success'))
            <div class="bg-white/90 backdrop-blur-sm border-l-4 border-emerald-400 text-gray-700 p-4 rounded-xl shadow-md flex items-center justify-between transition-all hover:shadow-lg">
                <div class="font-medium">{{ session('success') }}</div>
                <button onclick="this.parentElement.style.display='none'" class="text-gray-400 hover:text-gray-600 transition">✕</button>
            </div>
        @endif

        <!-- Stats Cards (unchanged) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="stat-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Products</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $products->count() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-red-300 to-transparent rounded-full"></div>
            </div>

            <div class="stat-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Variants</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $products->sum(function($p) { return $p->variants->count(); }) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-gray-300 to-transparent rounded-full"></div>
            </div>
        </div>

        <!-- Premium Gradient Tips Card (unchanged) -->
        <div class="tips-premium-gradient rounded-2xl p-6 relative">
            <div class="relative z-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl shadow-inner">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold gradient-title">Product Intelligence</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600">
                    <div class="flex items-start gap-2 p-2 rounded-lg transition-all hover:bg-white/50">
                        <span class="text-red-500 text-lg">✦</span> Use descriptive names for better search visibility
                    </div>
                    <div class="flex items-start gap-2 p-2 rounded-lg transition-all hover:bg-white/50">
                        <span class="text-red-500 text-lg">✦</span> Assign categories & brands to organize inventory
                    </div>
                    <div class="flex items-start gap-2 p-2 rounded-lg transition-all hover:bg-white/50">
                        <span class="text-red-500 text-lg">✦</span> Add variants (size/color) to track each SKU
                    </div>
                    <div class="flex items-start gap-2 p-2 rounded-lg transition-all hover:bg-white/50">
                        <span class="text-red-500 text-lg">✦</span> Monitor stock via the variant stock manager
                    </div>
                </div>
                <div class="mt-4 text-right text-xs text-gray-400 italic">Streamline your footwear catalog</div>
            </div>
        </div>

        <!-- Create Product Form - Centered -->
        <div class="create-product-container">
            <div class="card-cinematic rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                    <h3 class="text-md font-bold gradient-title">Create New Product</h3>
                </div>
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="product_name" class="block text-xs font-semibold text-gray-700 mb-1">Product Name <span class="text-red-400">*</span></label>
                        <input type="text" name="product_name" id="product_name" placeholder="e.g., Nike Air Max 90" required
                               class="input-compact">
                    </div>
                    <div>
                        <label for="product_description" class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                        <textarea name="product_description" id="product_description" rows="2" placeholder="Describe the product..."
                               class="input-compact resize-none"></textarea>
                    </div>
                    <div>
                        <label for="category_id" class="block text-xs font-semibold text-gray-700 mb-1">Category <span class="text-red-400">*</span></label>
                        <select name="category_id" id="category_id" required class="input-compact">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="brand_id" class="block text-xs font-semibold text-gray-700 mb-1">Brand <span class="text-red-400">*</span></label>
                        <select name="brand_id" id="brand_id" required class="input-compact">
                            <option value="">-- Select Brand --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="images" class="block text-xs font-semibold text-gray-700 mb-1">Product Images <span class="text-amber-500 text-xs">(Optional)</span></label>
                        <input type="file" name="images[]" id="images" accept="image/*" multiple
                               class="input-compact file:bg-gray-100 file:border-0 file:rounded file:cursor-pointer">
                        <p class="text-gray-500 text-xs mt-1">Upload multiple images (JPG, PNG, GIF, WebP - Max 5MB each)</p>
                    </div>
                    <button type="submit" class="btn-create-3d w-full py-2 rounded-lg font-semibold text-sm">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Product
                    </button>
                </form>
            </div>
        </div>

        <!-- Products List - Each product card strongly separated -->
        <div class="card-cinematic rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold gradient-title">All Products</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $products->count() }} total</span>
            </div>
            <div class="p-6">
                @forelse($products as $product)
                    <div class="product-card">
                        <!-- Product Header -->
                        <div class="px-5 py-3 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-md font-bold text-gray-800">{{ $product->product_name }}</h4>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">#{{ $product->product_id }}</span>
                                </div>
                                <div class="flex flex-wrap gap-3 text-xs text-gray-500 mt-1">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        {{ $product->category->category_name ?? 'Uncategorized' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        {{ $product->brand->brand_name ?? 'No brand' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        {{ $product->variants->count() }} variant(s)
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.products.edit', $product->product_id) }}" class="btn-sm-3d btn-sm-blue">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->product_id) }}" method="POST" onsubmit="return confirm('Delete product \'{{ addslashes($product->product_name) }}\'? All variants and stock will be removed.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm-3d btn-sm-red">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($product->product_description)
                            <div class="px-5 py-2 bg-gray-50/40 text-xs text-gray-600 border-b border-gray-100 italic">
                                {{ $product->product_description }}
                            </div>
                        @endif

                        <!-- Variants Section (the table) -->
                        <div class="px-5 py-3">
                            <h5 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                Variants (Size / Color)
                            </h5>

                            <form action="{{ route('admin.products.variants.store', $product->product_id) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="flex flex-wrap gap-2 items-end">
                                    <div class="flex-1 min-w-[100px]">
                                        <label for="size_{{ $product->product_id }}" class="block text-xs font-medium text-gray-600 mb-1">Size</label>
                                        <input type="text" name="size" id="size_{{ $product->product_id }}" placeholder="e.g., 42, M, XL" required
                                               class="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:border-gray-400 input-compact text-xs">
                                    </div>
                                    <div class="flex-1 min-w-[100px]">
                                        <label for="color_{{ $product->product_id }}" class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                        <input type="text" name="color" id="color_{{ $product->product_id }}" placeholder="e.g., Black, Red" required
                                               class="w-full px-2 py-1.5 border border-gray-200 rounded-lg focus:border-gray-400 input-compact text-xs">
                                    </div>
                                    <button type="submit" class="btn-sm-3d btn-sm-green mb-0.5 text-xs px-3 py-1.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Variant
                                    </button>
                                </div>
                            </form>

                            @if($product->variants->count())
                                <div class="overflow-x-auto custom-scroll">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Size</th>
                                                <th class="px-3 py-2 text-left">Color</th>
                                                <th class="px-3 py-2 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($product->variants->take(5) as $variant)
                                                <tr class="table-row-3d">
                                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $variant->size }}</td>
                                                    <td class="px-3 py-2 text-gray-600">{{ $variant->color }}</td>
                                                    <td class="px-3 py-2 text-right">
                                                        <div class="flex gap-1.5 justify-end">
                                                            <a href="{{ route('admin.variants.edit', $variant->product_variant_id) }}" class="btn-sm-3d btn-sm-blue text-xs px-2 py-1">
                                                                Edit
                                                            </a>
                                                            <form action="{{ route('admin.variants.destroy', $variant->product_variant_id) }}" method="POST" onsubmit="return confirm('Delete this variant? Stock data will be lost.');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn-sm-3d btn-sm-red text-xs px-2 py-1">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                            <a href="{{ route('admin.stocks.index', $variant->product_variant_id) }}" class="btn-sm-3d btn-sm-green text-xs px-2 py-1">
                                                                Manage Stocks
                                                            </a>
                                                            <a href="{{ route('admin.products.variants.index', $product->product_id) }}" class="btn-sm-3d btn-sm-green text-xs px-2 py-1">
                                                                Manage Variants
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if($product->variants->count() > 5)
    <div class="text-xs text-gray-500 mt-2">
        Showing 5 of {{ $product->variants->count() }} variants
        <a href="{{ route('admin.products.edit', $product->product_id) }}" class="text-blue-500 underline">
            View all
        </a>
    </div>
@endif
                                </div>
                            @else
                                <div class="text-center py-4 bg-gray-50/50 rounded-lg text-gray-500 text-xs">
                                    No variants yet. Add a size/color combination above.
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-500 bg-gray-50/30 rounded-xl text-sm">
                        No products found. Start by adding a product using the form above.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection