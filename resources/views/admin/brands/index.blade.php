@extends('layouts.admin')

@section('title', 'Brands')
@section('page-title', 'Brands Management')
@section('page-subtitle', 'Manage your product brands here.')

@section('styles')
    <!-- Tailwind CSS + Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f1f5f9 0%, #eef2f6 100%);
        }

        /* 3D Card effect - enhanced on hover */
        .card-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.03);
        }
        .card-3d:hover {
            transform: translateY(-6px) translateZ(12px) scale(1.02);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15), 0 4px 8px -4px rgba(0, 0, 0, 0.05);
        }

        /* Realistic 3D Button - White */
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

        /* Realistic 3D Button - White Small (Delete) */
        .btn-delete-3d-white {
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
        }
        .btn-delete-3d-white:active {
            transform: translateY(3px);
            box-shadow: 0 1px 0 #cbd5e1, 0 2px 6px rgba(0,0,0,0.04);
        }
        .btn-delete-3d-white:hover {
            background: #f9fafb;
            color: #1f2937;
            border-color: #d1d5db;
        }

        /* 3D Table Row - pops on hover */
        .table-row {
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 0 rgba(0,0,0,0.02);
        }
        .table-row:hover {
            transform: translateX(4px) translateY(-2px) translateZ(8px) scale(1.01);
            background: #ffffff;
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08), -3px 0 0 #cbd5e1;
            z-index: 2;
            position: relative;
        }

        /* 3D effect for the guidelines tip box */
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

        /* Input focus - neutral */
        .input-3d {
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02), 0 1px 1px rgba(0,0,0,0.01);
        }
        .input-3d:focus {
            transform: translateZ(2px);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05), inset 0 1px 2px rgba(0,0,0,0.02);
            border-color: #9ca3af;
            outline: none;
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

        <!-- Stats Cards with 3D hover -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="card-3d bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Brands</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $brands->count() }}</p>
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
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Active Brands</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $brands->where('brand_name', '!=', null)->count() }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-gray-200 rounded-full"></div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add Brand Form -->
            <div class="card-3d bg-white rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-5">Create New Brand</h3>
                <form action="{{ route('admin.brands.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="brand_name" class="block text-sm font-medium text-gray-700 mb-1">Brand Name <span class="text-gray-500">*</span></label>
                        <input type="text" name="brand_name" id="brand_name" placeholder="e.g., Nike, Adidas, Puma" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800">
                    </div>
                    <button type="submit" class="btn-3d-white w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Brand
                    </button>
                </form>
            </div>

            <!-- Guidelines Panel -->
            <div class="card-3d bg-white rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-5">Brand Guidelines</h3>
                <ul class="space-y-2 text-gray-600 text-sm">
                    <li class="flex items-start gap-2">• Use consistent, recognizable brand names.</li>
                    <li class="flex items-start gap-2">• Brands help customers filter products and build trust.</li>
                    <li class="flex items-start gap-2">• Deleting a brand will not delete products – they become unbranded.</li>
                    <li class="flex items-start gap-2">• Well-organized brands improve the shopping experience.</li>
                </ul>
                <div class="mt-5 pt-4 border-t border-gray-100">
                    <div class="tip-3d flex items-center gap-2 text-sm p-3 -mx-1 rounded-lg transition-all">
                        <span class="font-medium text-gray-800">Tip:</span>
                        <span class="text-gray-600">Add only active or relevant brands to keep the list clean.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brands Table -->
        <div class="card-3d bg-white rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">All Brands</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">{{ $brands->count() }} total</span>
            </div>
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Brand Name</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($brands as $brand)
                            <tr class="table-row">
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-3">
                                    <span class="font-medium text-gray-800">{{ $brand->brand_name }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form action="{{ route('admin.brands.destroy', $brand->brand_id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete brand \"{{ addslashes($brand->brand_name) }}\"? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-3d-white">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    No brands found. Create your first brand using the form.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection