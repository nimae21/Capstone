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

        .card-cinematic {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
        }

        .card-cinematic:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 35px -12px rgba(0,0,0,0.15);
        }

        .tips-premium-gradient {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,250,252,0.98));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            color: transparent;
        }

        .btn-create-3d {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 8px 0 #991b1b;
        }

        .btn-sm-3d {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
        }

        .btn-sm-blue { background:#3b82f6; color:white; }
        .btn-sm-red { background:#ef4444; color:white; }
        .btn-sm-green { background:#10b981; color:white; }

        .table-row-3d:hover {
            transform: translateX(4px);
            background: #fafcff;
        }

        .product-card {
            border: 1px solid #f0f0f0;
            background: white;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        .input-compact {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .input-compact:focus {
            border-color: #dc2626;
            outline: none;
        }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 relative z-10">

    <!-- Success -->
    @if(session('success'))
        <div class="bg-white border-l-4 border-emerald-400 p-4 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="p-6 bg-white rounded-xl">
            <p class="text-xs text-gray-500">Total Products</p>
            <p class="text-3xl font-bold">{{ $products->total() }}</p>
        </div>

        <div class="p-6 bg-white rounded-xl">
            <p class="text-xs text-gray-500">Total Variants</p>
            <p class="text-3xl font-bold">{{ $totalVariants }}</p>
        </div>
    </div>

    <!-- Tips -->
    <div class="tips-premium-gradient p-6 rounded-xl mb-6">
        <h3 class="gradient-title text-lg mb-3">Product Intelligence</h3>
        <p class="text-sm text-gray-600">Manage products efficiently and keep your inventory organized.</p>
    </div>

    <!-- Create Product -->
    <div class="max-w-md mx-auto mb-10">
        <div class="card-cinematic p-5 rounded-xl">
            <h3 class="gradient-title mb-4">Create Product</h3>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="text" name="product_name" placeholder="Product Name" required class="input-compact mb-3">

                <textarea name="product_description" placeholder="Description" class="input-compact mb-3"></textarea>

                <select name="category_id" class="input-compact mb-3" required>
                    <option value="">Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>

                <select name="brand_id" class="input-compact mb-3" required>
                    <option value="">Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                    @endforeach
                </select>

                <input type="file" name="images[]" multiple class="input-compact mb-3">

                <button class="btn-create-3d w-full py-2 rounded-lg">
                    Create Product
                </button>
            </form>
        </div>
    </div>

<!-- Search Bar -->
<div class="mb-6">
    <form method="GET" action="{{ route('admin.products.index') }}" class="flex gap-2">
        
        <input type="text" 
               name="search" 
               value="{{ request('search') }}"
               placeholder="Search products..."
               class="input-compact flex-1">

        <button onclick="this.innerText='Processing...'" type="submit" class="btn-create-3d px-4 rounded-lg">
            Search
        </button>

        @if(request('search'))
            <a onclick="this.innerText='Clearing...'" href="{{ route('admin.products.index') }}" 
               class="px-4 py-2 bg-gray-200 rounded-lg text-sm">
                Clear
            </a>

            
        @endif

    </form>
</div>

    <!-- Products -->
    <div class="bg-white rounded-xl p-6">
        <h3 class="gradient-title mb-4">All Products</h3>

        @forelse($products as $product)
            <div class="product-card">
                
                <!-- Header -->
                <div class="p-4 border-b flex justify-between">
                    <div>
                        <h4 class="font-bold">{{ $product->product_name }}</h4>
                        <p class="text-xs text-gray-500">
                            {{ $product->category->category_name ?? 'Uncategorized' }} • 
                            {{ $product->brand->brand_name ?? 'No Brand' }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.products.edit', $product->product_id) }}"
                           class="btn-sm-3d btn-sm-blue">Edit</a>

                        

                        <button type="button"
        onclick="openDeleteModal({{ $product->product_id }})"
        class="btn-sm-3d btn-sm-red">
    Delete
</button>

                        <a href="{{ route('admin.products.variants.index', $product->product_id) }}"
                           class="btn-sm-3d btn-sm-green">
                            Manage Variants
                        </a>
                    </div>
                </div>

                @if($product->product_description)
                    <div class="p-3 text-xs text-gray-600 bg-gray-50">
                        {{ $product->product_description }}
                    </div>
                @endif

            </div>
        @empty
    <p class="text-gray-500 text-sm">
        No products found{{ request('search') ? " for '".request('search')."'" : '' }}.
    </p>
@endforelse
        <div class="mt-6 flex justify-center">
    {{ $products->links() }}
</div>  
    </div>

</div>
@endsection

@if(session('success'))
<div id="successModal"
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-sm p-6 rounded-xl shadow-lg text-center">

        <div class="text-green-500 text-5xl mb-3">
            <i class="fas fa-check-circle"></i>
        </div>

        <h2 class="text-lg font-bold text-gray-800">
            Success!
        </h2>

        <p class="text-sm text-gray-600 mt-2">
            {{ session('success') }}
        </p>

        <button onclick="closeSuccessModal()"
                class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg">
            OK
        </button>

    </div>
</div>
@endif

<div id="deleteModal"
     style="display:none;"
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-md p-6 rounded-xl shadow-lg">

        <h2 class="text-lg font-bold text-gray-800">Confirm Delete</h2>

        <p class="text-sm text-gray-600 mt-2">
            Are you sure you want to delete this product? This action cannot be undone.
        </p>

        <form id="deleteForm" method="POST" class="mt-4">
            @csrf
            @method('DELETE')

            <div class="flex justify-end gap-2">

                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-200 rounded-lg">
                    Cancel
                </button>

                <button class="px-4 py-2 bg-red-500 text-white rounded-lg">
                    Yes, Delete
                </button>

            </div>
        </form>

    </div>
</div>

<script>
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) modal.style.display = 'none';
}

// Auto close after 3 seconds
window.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('successModal');

    if (modal) {
        setTimeout(() => {
            modal.style.display = 'none';
        }, 3000);
    }
});

function openDeleteModal(id) {
    document.getElementById('deleteForm').action = '/admin/products/' + id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// close when clicking outside modal
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>