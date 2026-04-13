@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Manage your online shoe store efficiently.')

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

        /* Realistic 3D Button - White (for Quick Actions) */
        .btn-3d-white {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #ffffff;
            color: #1f2937;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.05s linear;
            box-shadow: 0 6px 0 #cbd5e1, 0 3px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .btn-3d-white:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 #cbd5e1, 0 3px 8px rgba(0,0,0,0.05);
        }
        .btn-3d-white:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* 3D Table Row */
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

        /* Alert item 3D hover */
        .alert-item-3d {
            transition: all 0.2s ease;
            transform: translateZ(0);
            background: #fefefe;
        }
        .alert-item-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(4px);
            box-shadow: 0 6px 12px -6px rgba(0, 0, 0, 0.1);
            border-left-color: #9ca3af;
        }

        /* Status badges (no changes, keep as is but subtle) */
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status.completed {
            background: #d1fae5;
            color: #065f46;
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
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card-3d bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Products</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">124</p>
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
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Orders</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">58</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-gray-200 rounded-full"></div>
            </div>

            <div class="card-3d bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Registered Users</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">39</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-gray-200 rounded-full"></div>
            </div>

            <div class="card-3d bg-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Low Stock Items</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">7</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-gray-200 rounded-full"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="#" class="btn-3d-white">+ Add Product</a>
                <a href="#" class="btn-3d-white">+ Add Category</a>
                <a href="#" class="btn-3d-white">Manage Orders</a>
                <a href="#" class="btn-3d-white">Manage Users</a>
            </div>
        </div>

        <!-- Lower Grid: Recent Orders + Low Stock Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders Table -->
            <div class="card-3d bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto custom-scroll">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Order ID</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">#1001</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Juan Dela Cruz</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Nike Air Max</td>
                                <td class="px-6 py-3"><span class="status pending">Pending</span></td>
                            </tr>
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">#1002</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Maria Santos</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Adidas Samba</td>
                                <td class="px-6 py-3"><span class="status completed">Completed</span></td>
                            </tr>
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">#1003</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Kevin Reyes</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Puma RS-X</td>
                                <td class="px-6 py-3"><span class="status pending">Pending</span></td>
                            </tr>
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">#1004</td>
                                <td class="px-6 py-3 text-sm text-gray-600">Ana Lopez</td>
                                <td class="px-6 py-3 text-sm text-gray-600">New Balance 550</td>
                                <td class="px-6 py-3"><span class="status completed">Completed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="card-3d bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Low Stock Alerts</h3>
                </div>
                <div class="p-4 space-y-3">
                    <div class="alert-item-3d border-l-4 border-gray-300 p-4 rounded-lg transition-all">
                        <strong class="block text-gray-800 font-semibold">Nike Air Force 1</strong>
                        <span class="text-sm text-gray-500">Only 3 items left in stock</span>
                    </div>
                    <div class="alert-item-3d border-l-4 border-gray-300 p-4 rounded-lg transition-all">
                        <strong class="block text-gray-800 font-semibold">Adidas Ultraboost</strong>
                        <span class="text-sm text-gray-500">Only 2 items left in stock</span>
                    </div>
                    <div class="alert-item-3d border-l-4 border-gray-300 p-4 rounded-lg transition-all">
                        <strong class="block text-gray-800 font-semibold">Converse Chuck Taylor</strong>
                        <span class="text-sm text-gray-500">Only 5 items left in stock</span>
                    </div>
                    <div class="alert-item-3d border-l-4 border-gray-300 p-4 rounded-lg transition-all">
                        <strong class="block text-gray-800 font-semibold">Vans Old Skool</strong>
                        <span class="text-sm text-gray-500">Only 4 items left in stock</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection