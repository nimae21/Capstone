@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')
@section('page-subtitle', 'Manage your products, variants, and stock levels.')

@section('styles')
    <!-- Tailwind + Google Fonts -->
    <script src="https://cdn.tailwindcss.com">
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
        }

        /* Reuse dashboard card styles */
        .card-3d {
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.04);
            will-change: transform;
            backface-visibility: hidden;
        }
        .card-3d:hover {
            transform: translateY(-4px) translateZ(8px) scale(1.01);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12), 0 4px 8px -4px rgba(0, 0, 0, 0.04);
        }

        /* Gradient title - exactly like dashboard */
        .gradient-title {
            font-weight: 900 !important;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2.5rem;
            display: inline-block;
        }

        /* Section header underline */
        .section-header {
            font-weight: 800 !important;
            font-size: 1.15rem !important;
            letter-spacing: -0.01em;
            position: relative;
            display: inline-block;
        }
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #ef4444);
            border-radius: 3px;
        }

        /* ===== STAT CARDS (exactly like dashboard) ===== */
        .stat-card {
            position: relative;
            border-radius: 1rem;
            padding: 1.5rem 1.5rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            min-height: 120px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(2px);
        }

        .stat-card .stat-icon-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 6rem;
            opacity: 0.18;
            pointer-events: none;
            transform: rotate(8deg);
            line-height: 1;
            transition: opacity 0.3s ease;
        }
        .stat-card:hover .stat-icon-bg {
            opacity: 0.25;
        }

        .stat-card .stat-number {
            font-weight: 900 !important;
            font-size: 2.2rem !important;
            line-height: 1.2;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-label {
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            opacity: 0.75;
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-sub {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.25rem;
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-accent-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 0 4px 4px 0;
            z-index: 3;
        }

        /* Individual stat card color themes */
        .stat-blue {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
            border-left: 4px solid #3b82f6;
        }
        .stat-blue .stat-number {
            color: #1e40af;
        }
        .stat-blue .stat-label {
            color: #2563eb;
        }

        .stat-amber {
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
            border-left: 4px solid #f59e0b;
        }
        .stat-amber .stat-number {
            color: #92400e;
        }
        .stat-amber .stat-label {
            color: #d97706;
        }

        /* Form inputs */
        .input-compact {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
            color: #1e293b;
        }
        .input-compact:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
        }
        .input-compact::placeholder {
            color: #94a3b8;
        }
        select.input-compact {
            appearance: auto;
            cursor: pointer;
        }
        textarea.input-compact {
            resize: vertical;
            min-height: 80px;
        }

        /* Custom file input */
        .file-upload-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .file-upload-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }
        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            background: white;
            border: 2px solid #dc2626;
            color: #dc2626;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 0.875rem;
            white-space: nowrap;
            position: relative;
            z-index: 1;
        }
        .file-upload-btn:hover {
            background: #dc2626;
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .file-upload-wrapper:focus-within .file-upload-btn {
            outline: none;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15), 0 4px 12px rgba(220, 38, 38, 0.1);
            transform: scale(1.02);
            background: #fef2f2;
        }
        .file-upload-btn:active {
            transform: scale(0.96);
            background: #dc2626;
            color: white;
        }
        .file-upload-filename {
            font-size: 0.875rem;
            color: #475569;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 200px;
        }
        .file-upload-filename.empty {
            color: #94a3b8;
            font-style: italic;
        }

        .btn-create-3d {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-weight: 700;
            padding: 0.65rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 4px 0 #991b1b, 0 2px 8px rgba(0, 0, 0, 0.06);
            transform: translateY(0);
        }
        .btn-create-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #991b1b, 0 8px 16px -4px rgba(220, 38, 38, 0.2);
        }
        .btn-create-3d:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 #991b1b;
        }

        .btn-sm-3d {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.15s;
            transform: translateY(0);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-sm-3d:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-sm-blue {
            background: #3b82f6;
            color: white;
            box-shadow: 0 2px 0 #1d4ed8;
        }
        .btn-sm-blue:hover {
            background: #2563eb;
            color: white;
        }
        .btn-sm-red {
            background: #ef4444;
            color: white;
            box-shadow: 0 2px 0 #b91c1c;
        }
        .btn-sm-red:hover {
            background: #dc2626;
            color: white;
        }
        .btn-sm-green {
            background: #10b981;
            color: white;
            box-shadow: 0 2px 0 #047857;
        }
        .btn-sm-green:hover {
            background: #059669;
            color: white;
        }

        /* Product card */
        .product-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1.25rem;
            border: 1px solid #eef2f6;
            transition: all 0.25s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }
        .product-card:hover {
            border-color: #dc2626;
            box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        .product-card .product-header {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .product-card .product-body {
            padding: 1rem 1.5rem;
            background: #fafcfd;
            font-size: 0.875rem;
            color: #475569;
        }
        .product-card .product-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .create-card {
            background: white;
            border-radius: 1rem;
            padding: 1.75rem;
            border: 1px solid #eef2f6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        /* Search bar */
        .search-wrapper {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-wrapper .input-compact {
            flex: 1;
            min-width: 200px;
        }

        /* Pagination */
        .pagination-container {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .product-card .product-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .gradient-title {
                font-size: 2rem;
            }
            .stat-card .stat-number {
                font-size: 1.8rem !important;
            }
            .stat-card .stat-icon-bg {
                font-size: 4rem;
                right: -5px;
                bottom: -5px;
            }
            .file-upload-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
            .file-upload-filename {
                max-width: 100%;
                text-align: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">

        <!-- ===== HEADER ===== -->
        <div class="mb-6">
            <h1 class="gradient-title">Products</h1>
            <p class="text-gray-500 text-sm mt-1 flex items-center gap-2">
                <i class="fas fa-boxes text-gray-400"></i>
                Manage your products, variants, and stock levels.
            </p>
        </div>

        <!-- ===== SUCCESS MESSAGE ===== -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- ===== STATS CARDS (dashboard style) ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
            <!-- Total Products -->
            <div class="stat-card stat-blue card-3d">
                <div class="stat-accent-line" style="background:#3b82f6;"></div>
                <span class="stat-icon-bg">📦</span>
                <div class="flex-1">
                    <p class="stat-label">Total Products</p>
                    <p class="stat-number">{{ $totalProducts }}</p>
                    <p class="stat-sub">All products in catalog</p>
                </div>
            </div>

            <!-- Total Variants -->
            <div class="stat-card stat-amber card-3d">
                <div class="stat-accent-line" style="background:#f59e0b;"></div>
                <span class="stat-icon-bg">🔶</span>
                <div class="flex-1">
                    <p class="stat-label">Total Variants</p>
                    <p class="stat-number">{{ $totalVariants }}</p>
                    <p class="stat-sub">Product variations</p>
                </div>
            </div>
        </div>

        <!-- ===== CREATE PRODUCT ===== -->
        <div class="create-card card-3d mb-8">
            <h3 class="section-header text-gray-800 mb-4">Create Product</h3>
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input type="text" name="product_name" placeholder="Product Name" required class="input-compact">
                    </div>
                    <div>
                        <select name="category_id" id="category_id" class="input-compact" required data-addable>
    <option value="">Select Category</option>
    @foreach($categories as $category)
        <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
    @endforeach
    <option value="__add_new__">+ Add another Category</option>
</select>
                    </div>
                    <div>
                        <select name="brand_id" id="brand_id" class="input-compact" required data-addable>
    <option value="">Select Brand</option>
    @foreach($brands as $brand)
        <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
    @endforeach
    <option value="__add_new__">+ Add another Brand</option>
</select>
                    </div>

                    <div>

    <select name="shoe_type_id" id="shoe_type_id" class="input-compact" required data-addable>
    <option value="">Select Shoe Type</option>
    @foreach($shoeTypes as $shoeType)
    <option value="{{ $shoeType->shoe_type_id }}">{{ $shoeType->shoe_type_name }}</option>
@endforeach
    <option value="__add_new__">+ Add another Shoe Type</option>
</select>

</div>

                    <div>
    <div class="file-upload-wrapper">
        <div class="relative flex-1">
            <input type="file" name="images[]" multiple accept="image/*"
                   id="productImages" onchange="handleFileSelect(this)">
            <label for="productImages" class="file-upload-btn">
                <i class="fas fa-cloud-upload-alt"></i> Choose Images
            </label>
        </div>
        <span class="file-upload-filename empty" id="fileDisplay">No files selected</span>
    </div>

    <!-- Preview grid with remove buttons -->
    <div id="imagePreviewGrid" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3"></div>
</div>
                    <div class="md:col-span-2">
                        <textarea name="product_description" placeholder="Description (optional)" rows="2" class="input-compact"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="btn-create-3d flex items-center gap-2">
                            <i class="fas fa-plus-circle"></i> Create Product
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== SEARCH & PRODUCT LIST ===== -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <!-- Search -->
            <div class="mb-6">
                <form method="GET" action="{{ route('admin.products.index') }}">

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="md:col-span-4">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search product, brand, category or shoe type..."
                    class="input-compact pl-10">
            </div>
        </div>

        <div>
            <select name="category" class="input-compact">
                <option value="">All Categories</option>

                @foreach($categories as $category)
                    <option
                        value="{{ $category->category_id }}"
                        {{ request('category') == $category->category_id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="brand" class="input-compact">
                <option value="">All Brands</option>

                @foreach($brands as $brand)
                    <option
                        value="{{ $brand->brand_id }}"
                        {{ request('brand') == $brand->brand_id ? 'selected' : '' }}>
                        {{ $brand->brand_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="shoe_type" class="input-compact">
                <option value="">All Shoe Types</option>

                @foreach($shoeTypes as $shoeType)
                    <option
                        value="{{ $shoeType->shoe_type_id }}"
                        {{ request('shoe_type') == $shoeType->shoe_type_id ? 'selected' : '' }}>
                        {{ $shoeType->shoe_type_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">

            <button type="submit" class="btn-create-3d flex-1">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>

            <a href="{{ route('admin.products.index') }}"
               class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition flex items-center">
                Clear
            </a>

        </div>

    </div>

</form>
            </div>

            <!-- Product List -->
            <h3 class="section-header text-gray-800 mb-4">All Products</h3>

            @forelse($products as $product)
                <div class="product-card">
                    <div class="product-header">
                        <div>
                            <h4 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-tag text-red-500 text-sm"></i>
                                {{ $product->product_name }}
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $product->category->category_name ?? 'Uncategorized' }}
•
{{ $product->brand->brand_name ?? 'No Brand' }}
•
{{ $product->shoeType->shoe_type_name ?? 'No Shoe Type' }}
                            </p>
                        </div>
                        <div class="product-actions">
                            <a href="{{ route('admin.products.edit', $product->product_id) }}"
                               class="btn-sm-3d btn-sm-blue">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button"
                                    onclick="openDeleteModal({{ $product->product_id }})"
                                    class="btn-sm-3d btn-sm-red">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                            <a href="{{ route('admin.products.variants.index', $product->product_id) }}"
                               class="btn-sm-3d btn-sm-green">
                                <i class="fas fa-cubes"></i> Variants
                            </a>
                        </div>
                    </div>
                    @if($product->product_description)
                        <div class="product-body">
                            <i class="fas fa-align-left text-gray-400 mr-2"></i>
                            {{ $product->product_description }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-box-open text-5xl mb-3 block opacity-30"></i>
                    <p class="text-lg font-medium">No products found</p>
                    @if(request('search'))
                        <p class="text-sm">No results for "{{ request('search') }}"</p>
                    @else
                        <p class="text-sm">Start by creating your first product above.</p>
                    @endif
                </div>
            @endforelse

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        </div>

    </div>

    <!-- ===== DELETE MODAL ===== -->
    <div id="deleteModal" style="display:none;" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-md p-6 rounded-xl shadow-lg">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Confirm Delete</h2>
            </div>
            <p class="text-sm text-gray-600">
                Are you sure you want to delete this product? This action cannot be undone.
            </p>
            <form id="deleteForm" method="POST" class="mt-6 flex justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Yes, Delete
                </button>
            </form>
        </div>
    </div>

    <!-- ===== ADD NEW (Brand/Category/ShoeType) MODAL ===== -->
<div id="addNewModal" style="display:none;" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-md p-6 rounded-xl shadow-lg">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                <i class="fas fa-plus-circle text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-800" id="addNewModalTitle">Add New</h2>
        </div>

        <div id="addNewModalErrors" class="hidden bg-red-50 border-l-4 border-red-500 text-red-700 text-sm p-3 rounded-lg mb-4"></div>

        <div id="addNewModalFields" class="space-y-3"></div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeAddNewModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Cancel
            </button>
            <button type="button" onclick="submitAddNew()" id="addNewSubmitBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                Add
            </button>
        </div>
    </div>
</div>

    <script>
        function openDeleteModal(id) {
            document.getElementById('deleteForm').action = '/admin/products/' + id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal on outside click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Auto-close success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successDiv = document.querySelector('.bg-emerald-50');
            if (successDiv) {
                setTimeout(() => {
                    successDiv.style.transition = 'opacity 0.5s';
                    successDiv.style.opacity = '0';
                    setTimeout(() => successDiv.remove(), 500);
                }, 5000);
            }
        });

        
        let selectedFiles = [];

function handleFileSelect(input) {
    // Append newly chosen files to what's already selected
    selectedFiles = selectedFiles.concat(Array.from(input.files));
    syncFileInput();
    renderPreviews();
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    syncFileInput();
    renderPreviews();
}

function syncFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    document.getElementById('productImages').files = dataTransfer.files;

    const display = document.getElementById('fileDisplay');
    if (selectedFiles.length === 0) {
        display.textContent = 'No files selected';
        display.className = 'file-upload-filename empty';
    } else {
        display.textContent = selectedFiles.length + ' file(s) selected';
        display.className = 'file-upload-filename';
    }
}

function renderPreviews() {
    const grid = document.getElementById('imagePreviewGrid');
    grid.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const url = URL.createObjectURL(file);

        const card = document.createElement('div');
        card.className = 'relative border rounded-lg overflow-hidden bg-white shadow-sm';
        card.innerHTML = `
            <img src="${url}" class="w-full h-24 object-cover">
            <button type="button" onclick="removeFile(${index})"
                    class="absolute top-1 right-1 w-6 h-6 bg-red-600 hover:bg-red-700 text-white rounded-full flex items-center justify-center text-xs shadow">
                <i class="fas fa-times"></i>
            </button>
        `;
        grid.appendChild(card);
    });
}

        const addNewConfig = {
    brand_id: {
        title: 'Add New Brand',
        url: '{{ route('admin.brands.store') }}',
        fields: [
            { name: 'brand_name', label: 'Brand Name', placeholder: 'e.g., Nike, Adidas', required: true },
        ],
    },
    category_id: {
        title: 'Add New Category',
        url: '{{ route('admin.categories.store') }}',
        fields: [
            { name: 'category_name', label: 'Category Name', placeholder: 'e.g., Running Shoes', required: true },
            { name: 'category_description', label: 'Description', placeholder: 'Optional', required: false },
        ],
    },
    shoe_type_id: {
        title: 'Add New Shoe Type',
        url: '{{ route('admin.shoe-types.store') }}',
        fields: [
            { name: 'shoe_type_name', label: 'Shoe Type Name', placeholder: 'e.g., Sneakers, Boots', required: true },
            { name: 'description', label: 'Description', placeholder: 'Optional', required: false },
        ],
    },
};

let addNewActiveSelect = null;

document.querySelectorAll('select[data-addable]').forEach(select => {
    select.dataset.lastValue = select.value;

    select.addEventListener('change', function () {
        if (this.value === '__add_new__') {
            openAddNewModal(this);
        } else {
            this.dataset.lastValue = this.value;
        }
    });
});

function openAddNewModal(selectEl) {
    addNewActiveSelect = selectEl;
    const config = addNewConfig[selectEl.id];

    document.getElementById('addNewModalTitle').textContent = config.title;
    document.getElementById('addNewModalErrors').classList.add('hidden');
    document.getElementById('addNewModalErrors').innerHTML = '';

    const fieldsContainer = document.getElementById('addNewModalFields');
    fieldsContainer.innerHTML = config.fields.map(f => `
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">${f.label}${f.required ? ' <span class="text-red-500">*</span>' : ''}</label>
            <input type="text" name="${f.name}" placeholder="${f.placeholder}" ${f.required ? 'required' : ''} class="input-compact">
        </div>
    `).join('');

    document.getElementById('addNewModal').style.display = 'flex';
}

function closeAddNewModal() {
    if (addNewActiveSelect) {
        addNewActiveSelect.value = addNewActiveSelect.dataset.lastValue;
    }
    document.getElementById('addNewModal').style.display = 'none';
    addNewActiveSelect = null;
}

function submitAddNew() {
    if (!addNewActiveSelect) return;

    const config = addNewConfig[addNewActiveSelect.id];
    const payload = {};
    let valid = true;

    document.querySelectorAll('#addNewModalFields input').forEach(input => {
        if (input.required && !input.value.trim()) valid = false;
        payload[input.name] = input.value.trim();
    });

    if (!valid) {
        showAddNewError('Please fill in all required fields.');
        return;
    }

    const token = document.querySelector('#addToCartForm') // fallback not needed
        ? null : null;
    const csrfToken = document.querySelector('input[name="_token"]').value;

    const submitBtn = document.getElementById('addNewSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';

    fetch(config.url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    })
    .then(data => {
        const option = document.createElement('option');
        option.value = data.id;
        option.textContent = data.name;

        const sentinel = addNewActiveSelect.querySelector('option[value="__add_new__"]');
        addNewActiveSelect.insertBefore(option, sentinel);

        addNewActiveSelect.value = data.id;
        addNewActiveSelect.dataset.lastValue = data.id;

        document.getElementById('addNewModal').style.display = 'none';
        addNewActiveSelect = null;
    })
    .catch(err => {
        const message = err.errors
            ? Object.values(err.errors).flat().join(' ')
            : (err.message || 'Something went wrong. Please try again.');
        showAddNewError(message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add';
    });
}

function showAddNewError(message) {
    const errBox = document.getElementById('addNewModalErrors');
    errBox.textContent = message;
    errBox.classList.remove('hidden');
}

document.getElementById('addNewModal').addEventListener('click', function (e) {
    if (e.target === this) closeAddNewModal();
});

    </script>
@endsection