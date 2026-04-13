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
            background: linear-gradient(145deg, #f1f5f9 0%, #eef2f6 100%);
        }

        /* 3D Card effect */
        .card-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.03);
        }
        .card-3d:hover {
            transform: translateY(-6px) translateZ(12px) scale(1.02);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15), 0 4px 8px -4px rgba(0, 0, 0, 0.05);
        }

        /* Realistic 3D Button - White (Primary) */
        .btn-3d-white {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #ffffff;
            color: #1f2937;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.05s linear;
            box-shadow: 0 6px 0 #cbd5e1, 0 3px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .btn-3d-white:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 #cbd5e1, 0 3px 8px rgba(0,0,0,0.05);
        }
        .btn-3d-white:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* Small 3D White Button (for Add Variant, Edit, Delete, Manage Stocks) */
        .btn-sm-3d-white {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            background: #ffffff;
            color: #4b5563;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.05s linear;
            box-shadow: 0 4px 0 #cbd5e1, 0 2px 6px rgba(0,0,0,0.08);
            transform: translateY(-1px);
            text-decoration: none;
        }
        .btn-sm-3d-white:active {
            transform: translateY(3px);
            box-shadow: 0 1px 0 #cbd5e1, 0 2px 6px rgba(0,0,0,0.04);
        }
        .btn-sm-3d-white:hover {
            background: #f9fafb;
            color: #1f2937;
            border-color: #d1d5db;
        }

        /* Special green variant for Manage Stocks (keep as accent but still 3D white base) */
        .btn-stock-3d {
            background: #ffffff;
            color: #2b6e3b;
            border-color: #d1fae5;
        }
        .btn-stock-3d:hover {
            background: #f0fdf4;
            color: #166534;
        }

        /* 3D Table Row (for variants table) */
        .table-row-3d {
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .table-row-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(8px) scale(1.01);
            background: #ffffff;
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08), -3px 0 0 #cbd5e1;
            z-index: 2;
            position: relative;
        }

        /* 3D effect for product card */
        .product-card-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .product-card-3d:hover {
            transform: translateY(-4px) translateZ(8px) scale(1.01);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.1);
        }

        /* Input/Select/Textarea 3D focus */
        .input-3d, select.input-3d, textarea.input-3d {
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02), 0 1px 1px rgba(0,0,0,0.01);
        }
        .input-3d:focus, select.input-3d:focus, textarea.input-3d:focus {
            transform: translateZ(2px);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05), inset 0 1px 2px rgba(0,0,0,0.02);
            border-color: #9ca3af;
            outline: none;
        }

        /* Tip box 3D */
        .tip-3d {
            transition: all 0.2s ease;
            transform: translateZ(0);
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .tip-3d:hover {
            transform: translateY(-2px) translateZ(4px);
            box-shadow: 0 6px 12px -6px rgba(0, 0, 0, 0.1);
            background: #fefefe;
        }

        /* Scrollbar */
        .custom-scroll::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Success Alert -->
        @if(session('success'))
            <div class="bg-white border-l-4 border-gray-400 text-gray-700 p-4 rounded-lg shadow-md flex items-center justify-between transition-all hover:shadow-lg hover:translate-y-px">
                <div class="font-medium">{{ session('success') }}</div>
                <button onclick="this.parentElement.style.display='none'" class="text-gray-400 hover:text-gray-600 transition">×</button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="card-3d bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Products</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $products->count() }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-gray-200 rounded-full"></div>
            </div>

            <div class="card-3d bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Variants</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $products->sum(function($p) { return $p->variants->count(); }) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-gray-200 rounded-full"></div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add Product Form -->
            <div class="card-3d bg-white rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-5">Create New Product</h3>
                <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-gray-500">*</span></label>
                        <input type="text" name="product_name" id="product_name" placeholder="e.g., Nike Air Max 90" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800">
                    </div>
                    <div>
                        <label for="product_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="product_description" id="product_description" rows="3" placeholder="Describe the product..."
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800"></textarea>
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-gray-500">*</span></label>
                        <select name="category_id" id="category_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand <span class="text-gray-500">*</span></label>
                        <select name="brand_id" id="brand_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800">
                            <option value="">-- Select Brand --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-3d-white w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Product
                    </button>
                </form>
            </div>

            <!-- Guidelines Panel -->
            <div class="card-3d bg-white rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-5">Product Management Tips</h3>
                <ul class="space-y-2 text-gray-600 text-sm">
                    <li class="flex items-start gap-2">• Add clear, descriptive product names.</li>
                    <li class="flex items-start gap-2">• Assign each product to a category and brand for better navigation.</li>
                    <li class="flex items-start gap-2">• After creating a product, add variants (size/color) and manage stock per variant.</li>
                    <li class="flex items-start gap-2">• Deleting a product also removes all its variants and stock records.</li>
                </ul>
                <div class="mt-5 pt-4 border-t border-gray-100">
                    <div class="tip-3d flex items-center gap-2 text-sm p-3 -mx-1 rounded-lg transition-all">
                        <span class="font-medium text-gray-800">Pro tip:</span>
                        <span class="text-gray-600">Use the "Manage Stocks" link to set quantities for each variant.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products List (Card based with 3D effects) -->
        <div class="card-3d bg-white rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">All Products</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">{{ $products->count() }} total</span>
            </div>
            <div class="p-6 space-y-6">
                @forelse($products as $product)
                    <div class="product-card-3d bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                        <!-- Product Header -->
                        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h4 class="text-lg font-bold text-gray-800">{{ $product->product_name }}</h4>
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
                                <a href="{{ route('admin.products.edit', $product->product_id) }}" class="btn-sm-3d-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->product_id) }}" method="POST" onsubmit="return confirm('Delete product \'{{ addslashes($product->product_name) }}\'? All variants and stock will be removed.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm-3d-white">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Description (if exists) -->
                        @if($product->product_description)
                            <div class="px-5 py-3 bg-gray-50/50 text-sm text-gray-600 border-b border-gray-100">
                                {{ $product->product_description }}
                            </div>
                        @endif

                        <!-- Variants Section -->
                        <div class="px-5 py-4">
                            <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                Variants (Size / Color)
                            </h5>

                            <!-- Add Variant Form -->
                            <form action="{{ route('admin.products.variants.store', $product->product_id) }}" method="POST" class="mb-5">
                                @csrf
                                <div class="flex flex-wrap gap-3 items-end">
                                    <div class="flex-1 min-w-[120px]">
                                        <label for="size_{{ $product->product_id }}" class="block text-xs font-medium text-gray-600 mb-1">Size</label>
                                        <input type="text" name="size" id="size_{{ $product->product_id }}" placeholder="e.g., 42, M, XL" required
                                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800 text-sm">
                                    </div>
                                    <div class="flex-1 min-w-[120px]">
                                        <label for="color_{{ $product->product_id }}" class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                        <input type="text" name="color" id="color_{{ $product->product_id }}" placeholder="e.g., Black, Red" required
                                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800 text-sm">
                                    </div>
                                    <button type="submit" class="btn-sm-3d-white mb-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Variant
                                    </button>
                                </div>
                            </form>

                            <!-- Variants Table -->
                            @if($product->variants->count())
                                <div class="overflow-x-auto custom-scroll">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-2 text-left">Size</th>
                                                <th class="px-4 py-2 text-left">Color</th>
                                                <th class="px-4 py-2 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($product->variants as $variant)
                                                <tr class="table-row-3d">
                                                    <td class="px-4 py-2 font-medium text-gray-800">{{ $variant->size }}</td>
                                                    <td class="px-4 py-2 text-gray-600">{{ $variant->color }}</td>
                                                    <td class="px-4 py-2 text-right">
                                                        <div class="flex gap-2 justify-end">
                                                            <a href="{{ route('admin.variants.edit', $variant->product_variant_id) }}" class="btn-sm-3d-white">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                                Edit
                                                            </a>
                                                            <form action="{{ route('admin.variants.destroy', $variant->product_variant_id) }}" method="POST" onsubmit="return confirm('Delete this variant? Stock data will be lost.');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn-sm-3d-white">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                            <a href="{{ route('admin.stocks.index', $variant->product_variant_id) }}" class="btn-sm-3d-white btn-stock-3d">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                                Manage Stocks
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6 bg-gray-50/50 rounded-lg text-gray-500 text-sm">
                                    No variants yet. Add a size/color combination above.
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        No products found. Start by adding a product using the form on the left.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection