@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')
@section('page-subtitle', 'Organize your products with categories.')

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

        /* ===== STAT CARDS ===== */
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

        .stat-purple {
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
            border-left: 4px solid #8b5cf6;
        }
        .stat-purple .stat-number {
            color: #5b21b6;
        }
        .stat-purple .stat-label {
            color: #7c3aed;
        }

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

        /* ===== CATEGORY ITEM with LEFT background icon ===== */
        .category-item {
            position: relative;
            background: white;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem 1.25rem 1.5rem;
            margin-bottom: 0.75rem;
            border: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.25s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            min-height: 80px;
        }
        .category-item:hover {
            box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        /* Background icon - now on the LEFT side */
        .category-item .category-icon-bg {
            position: absolute;
            left: -5px;
            bottom: -5px;
            font-size: 5rem;
            opacity: 0.12;
            pointer-events: none;
            transform: rotate(-6deg);
            line-height: 1;
            transition: opacity 0.3s ease;
        }
        .category-item:hover .category-icon-bg {
            opacity: 0.18;
        }

        .category-item .category-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 2;
            flex: 1;
            padding-left: 0.5rem;
        }

        .category-item .category-badge {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .category-item .category-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 1rem;
        }

        .category-item .category-desc {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.1rem;
        }

        .category-item .category-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        /* Accent border colors based on category type */
        .category-item.border-men {
            border-left: 4px solid #3b82f6;
        }
        .category-item.border-women {
            border-left: 4px solid #ec4899;
        }
        .category-item.border-sale {
            border-left: 4px solid #ef4444;
        }
        .category-item.border-fresh {
            border-left: 4px solid #f59e0b;
        }
        .category-item.border-unisex {
            border-left: 4px solid #8b5cf6;
        }
        .category-item.border-default {
            border-left: 4px solid #94a3b8;
        }

        /* Badge colors */
        .badge-men {
            background: #3b82f6;
        }
        .badge-women {
            background: #ec4899;
        }
        .badge-sale {
            background: #ef4444;
        }
        .badge-fresh {
            background: #f59e0b;
        }
        .badge-unisex {
            background: #8b5cf6;
        }
        .badge-default {
            background: #94a3b8;
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

        .create-card {
            background: white;
            border-radius: 1rem;
            padding: 1.75rem;
            border: 1px solid #eef2f6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        /* Responsive */
        @media (max-width: 640px) {
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
            .category-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1.25rem 1rem;
            }
            .category-item .category-actions {
                width: 100%;
                justify-content: flex-start;
            }
            .category-item .category-icon-bg {
                font-size: 3.5rem;
                left: -5px;
                bottom: -5px;
            }
            .category-item .category-info {
                padding-left: 0.25rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">

        <!-- ===== HEADER ===== -->
        <div class="mb-6">
            <h1 class="gradient-title">Categories</h1>
            <p class="text-gray-500 text-sm mt-1 flex items-center gap-2">
                <i class="fas fa-tags text-gray-400"></i>
                Organize your products with categories.
            </p>
        </div>

        <!-- ===== SUCCESS MESSAGE ===== -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- ===== STAT CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
            <div class="stat-card stat-purple card-3d">
                <div class="stat-accent-line" style="background:#8b5cf6;"></div>
                <span class="stat-icon-bg">🏷️</span>
                <div class="flex-1">
                    <p class="stat-label">Total Categories</p>
                    <p class="stat-number">{{ $categories->total() ?? $categories->count() ?? 0 }}</p>
                    <p class="stat-sub">Organize your products</p>
                </div>
            </div>
            <div class="stat-card stat-blue card-3d">
                <div class="stat-accent-line" style="background:#3b82f6;"></div>
                <span class="stat-icon-bg">📊</span>
                <div class="flex-1">
                    <p class="stat-label">Category Usage</p>
                    <p class="stat-number">{{ $categories->count() > 0 ? round(\App\Models\Product::count() / $categories->count(), 1) : 0 }}</p>
                    <p class="stat-sub">Avg. products per category</p>
                </div>
            </div>
        </div>

        <!-- ===== CREATE CATEGORY ===== -->
        <div class="create-card card-3d mb-8">
            <h3 class="section-header text-gray-800 mb-4">Create Category</h3>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="category_name" placeholder="Category Name" required class="input-compact">
                    </div>
                    <div class="flex-1">
                        <input type="text" name="category_description" placeholder="Description (optional)" class="input-compact">
                    </div>
                    <button type="submit" class="btn-create-3d flex items-center gap-2 whitespace-nowrap">
                        <i class="fas fa-plus-circle"></i> Add Category
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== CATEGORY LIST ===== -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <h3 class="section-header text-gray-800 mb-4">All Categories</h3>

            @forelse($categories as $category)
                @php
                    // Determine category type and icon based on name
                    $name = strtolower($category->category_name);
                    $icon = '📁';
                    $type = 'default';
                    
                    if (str_contains($name, 'men') || str_contains($name, 'male') || str_contains($name, 'boy') || str_contains($name, 'guy')) {
                        $icon = '👞';
                        $type = 'men';
                    } elseif (str_contains($name, 'women') || str_contains($name, 'female') || str_contains($name, 'girl') || str_contains($name, 'lady')) {
                        $icon = '👠';
                        $type = 'women';
                    } elseif (str_contains($name, 'sale') || str_contains($name, 'discount') || str_contains($name, 'offer')) {
                        $icon = '🔥';
                        $type = 'sale';
                    } elseif (str_contains($name, 'fresh') || str_contains($name, 'new') || str_contains($name, 'drop')) {
                        $icon = '✨';
                        $type = 'fresh';
                    } elseif (str_contains($name, 'unisex') || str_contains($name, 'both')) {
                        $icon = '🧍';
                        $type = 'unisex';
                    } else {
                        $icon = '📂';
                        $type = 'default';
                    }
                @endphp

                <div class="category-item border-{{ $type }}">
                    <!-- Background icon on the LEFT -->
                    <span class="category-icon-bg">{{ $icon }}</span>

                    <div class="category-info">
                        <span class="category-badge badge-{{ $type }}"></span>
                        <div>
                            <div class="category-name">{{ $category->category_name }}</div>
                            @if($category->category_description)
                                <div class="category-desc">{{ $category->category_description }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="category-actions">
                        <a href="{{ route('admin.categories.edit', $category->category_id) }}" 
                           class="btn-sm-3d btn-sm-blue">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" 
                                onclick="openDeleteModal({{ $category->category_id }})"
                                class="btn-sm-3d btn-sm-red">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-folder-open text-5xl mb-3 block opacity-30"></i>
                    <p class="text-lg font-medium">No categories yet</p>
                    <p class="text-sm">Start by creating your first category above.</p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if(method_exists($categories, 'links'))
                <div class="pagination-container mt-4 flex justify-center">
                    {{ $categories->links() }}
                </div>
            @endif
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
                Are you sure you want to delete this category? This action cannot be undone.
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

    <script>
        function openDeleteModal(id) {
            document.getElementById('deleteForm').action = '/admin/categories/' + id;
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
    </script>
@endsection