@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Manage your online shoe store efficiently.')

@section('styles')
    <!-- Tailwind CSS + Google Fonts -->
    <script src="https://cdn.tailwindcss.com">
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js">
    </script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(145deg, #f1f5f9 0%, #eef2f6 100%);
            min-height: 100vh;
        }

        h1,
        h2,
        h3,
        h4,
        .title-bold,
        .page-title,
        .card-title,
        .stat-label {
            font-weight: 800 !important;
            letter-spacing: -0.01em;
        }

        /* --- Enhanced 3D Card --- */
        .card-3d {
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.04);
            will-change: transform;
            backface-visibility: hidden;
        }
        .card-3d:hover {
            transform: translateY(-6px) translateZ(12px) scale(1.01);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.13), 0 4px 8px -4px rgba(0, 0, 0, 0.04);
        }

        /* --- Stat Card --- */
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

        /* --- More visible background icons --- */
        .stat-card .stat-icon-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 6rem;
            opacity: 0.18;
            /* increased opacity */
            pointer-events: none;
            transform: rotate(8deg);
            line-height: 1;
            transition: opacity 0.3s ease;
        }
        .stat-card:hover .stat-icon-bg {
            opacity: 0.25;
            /* slightly brighter on hover */
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

        /* Individual stat card color themes (keep backgrounds and accents) */
        .stat-blue {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
            border-left: 4px solid #3b82f6;
        }
        .stat-blue .stat-number {
            color: #1e40af;
        }

        .stat-green {
            background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
            border-left: 4px solid #10b981;
        }
        .stat-green .stat-number {
            color: #065f46;
        }

        .stat-purple {
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
            border-left: 4px solid #8b5cf6;
        }
        .stat-purple .stat-number {
            color: #5b21b6;
        }

        .stat-red {
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            border-left: 4px solid #ef4444;
        }
        .stat-red .stat-number {
            color: #991b1b;
        }

        .stat-amber {
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
            border-left: 4px solid #f59e0b;
        }
        .stat-amber .stat-number {
            color: #92400e;
        }

        .stat-cyan {
            background: linear-gradient(135deg, #ffffff 0%, #ecfeff 100%);
            border-left: 4px solid #06b6d4;
        }
        .stat-cyan .stat-number {
            color: #155e75;
        }

        .stat-indigo {
            background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
            border-left: 4px solid #6366f1;
        }
        .stat-indigo .stat-number {
            color: #3730a3;
        }

        .stat-rose {
            background: linear-gradient(135deg, #ffffff 0%, #fff1f2 100%);
            border-left: 4px solid #f43f5e;
        }
        .stat-rose .stat-number {
            color: #9f1239;
        }

        /* --- Quick Action Buttons (distinct colors) --- */
        .btn-action {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 700;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateY(0);
            box-shadow: 0 4px 0 rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.06);
            text-decoration: none;
            color: white;
        }
        .btn-action:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -6px rgba(0, 0, 0, 0.18);
        }

        /* Individual colors */
        .btn-green {
            background: #10b981;
            border-color: #059669;
            box-shadow: 0 4px 0 #047857, 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .btn-green:hover {
            background: #059669;
            border-color: #047857;
        }

        .btn-blue {
            background: #3b82f6;
            border-color: #2563eb;
            box-shadow: 0 4px 0 #1d4ed8, 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .btn-blue:hover {
            background: #2563eb;
            border-color: #1d4ed8;
        }

        .btn-amber {
            background: #f59e0b;
            border-color: #d97706;
            box-shadow: 0 4px 0 #b45309, 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .btn-amber:hover {
            background: #d97706;
            border-color: #b45309;
        }

        .btn-purple {
            background: #8b5cf6;
            border-color: #7c3aed;
            box-shadow: 0 4px 0 #6d28d9, 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .btn-purple:hover {
            background: #7c3aed;
            border-color: #6d28d9;
        }

        /* Logout button remains the same */
        .btn-3d-logout {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #1e293b;
            font-weight: 600;
            padding: 0.375rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-size: 0.813rem;
        }

        .btn-3d-logout:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #f87171;
            color: #991b1b;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -4px rgba(220, 38, 38, 0.2);
        }

        /* --- Admin Badge --- */
        .admin-badge {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.375rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.813rem;
            color: #1e293b;
        }

        .admin-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.6;
                transform: scale(1.1);
            }
        }

        /* --- Table Row --- */
        .table-row-3d {
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            border-bottom: 2px solid #e5e7eb;
        }
        .table-row-3d:last-child {
            border-bottom: none;
        }
        .table-row-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(8px) scale(1.01);
            background: #ffffff;
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.07), -3px 0 0 #cbd5e1;
            z-index: 2;
            position: relative;
            border-bottom-color: #94a3b8;
        }

        /* --- Status Badges --- */
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-block;
            text-transform: capitalize;
            letter-spacing: 0.02em;
        }
        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status.completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status.processing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .status.shipped {
            background: #e0e7ff;
            color: #3730a3;
        }
        .status.refunded {
            background: #fce7f3;
            color: #9d174d;
        }
        .status.paid {
            background: #d1fae5;
            color: #065f46;
        }

        /* --- Custom Scroll --- */
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

        /* --- Welcome Title --- */
        .welcome-title {
            font-weight: 900 !important;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2rem;
        }

        /* --- Section Header --- */
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
            bottom: -8px;
            left: 0;
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #ef4444);
            border-radius: 3px;
        }

        /* --- Low Stock Alert Item --- */
        .alert-item-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border-left: 4px solid #f87171;
            background: #fef2f2;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .alert-item-3d:last-child {
            margin-bottom: 0;
        }
        .alert-item-3d:hover {
            transform: translateX(4px) translateY(-2px);
            box-shadow: 0 4px 12px -4px rgba(239, 68, 68, 0.12);
            background: #ffffff;
        }

        /* --- Low Stock container: remove max-height to show all items --- */
        .low-stock-container {
            max-height: none;
            overflow-y: visible;
        }

        /* --- Responsive tweaks --- */
        @media (max-width: 640px) {
            .stat-card .stat-number {
                font-size: 1.6rem !important;
            }
            .welcome-title {
                font-size: 1.4rem !important;
            }
            .stat-card {
                min-height: 100px;
                padding: 1rem;
            }
            .stat-card .stat-icon-bg {
                font-size: 4rem;
                right: -5px;
                bottom: -5px;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .stat-card .stat-number {
                font-size: 1.9rem !important;
            }
        }

        .stat-card-wrapper {
            height: 100%;
        }

        /* --- Recent Orders table --- */
        .orders-table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.04em;
            color: #64748b;
            border-bottom: 2px solid #d1d5db;
            padding: 0.75rem 1.5rem;
        }

        .orders-table td {
            padding: 0.875rem 1.5rem;
            vertical-align: middle;
        }

        .orders-table tbody tr {
            border-bottom: 2px solid #e5e7eb;
            transition: background 0.15s ease;
        }
        .orders-table tbody tr:last-child {
            border-bottom: none;
        }
        .orders-table tbody tr:hover {
            background: #f8fafc;
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: auto;
            min-height: 200px;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-grid max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-7">

        <!-- ========== WELCOME CARD ========== -->
        <div class="card-3d bg-white rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h1 class="welcome-title">Welcome Back, {{ Auth::user()->name }}!</h1>
                        <p class="text-gray-500 mt-1 text-sm">Here's what's happening with your store today.</p>
                    </div>
                    <div class="admin-info-bar flex items-center gap-3 flex-wrap">
                        <div class="admin-badge">
                            <span class="admin-dot"></span>
                            <span>Logged as Admin</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-3d-logout">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== STATS CARDS (8 cards) with larger, more visible background icons ========== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- 1. Total Products -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-blue card-3d">
                    <div class="stat-accent-line" style="background:#3b82f6;"></div>
                    <span class="stat-icon-bg">📦</span>
                    <div class="flex-1">
                        <p class="stat-label text-blue-700">Total Products</p>
                        <p class="stat-number">{{ $totalProducts }}</p>
                        <p class="stat-sub text-blue-600">+{{ $newProducts }} new this month</p>
                    </div>
                </div>
            </div>

            <!-- 2. Total Orders -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-green card-3d">
                    <div class="stat-accent-line" style="background:#10b981;"></div>
                    <span class="stat-icon-bg">🛒</span>
                    <div class="flex-1">
                        <p class="stat-label text-green-700">Total Orders</p>
                        <p class="stat-number">{{ $totalOrders }}</p>
                        <p class="stat-sub text-green-600">
                            @if($ordersGrowth >= 0)
                                <span class="trend-up">↑ {{ number_format($ordersGrowth, 1) }}%</span>
                            @else
                                <span class="trend-down">↓ {{ number_format(abs($ordersGrowth), 1) }}%</span>
                            @endif
                            from last month
                        </p>
                    </div>
                </div>
            </div>

            <!-- 3. Registered Users -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-purple card-3d">
                    <div class="stat-accent-line" style="background:#8b5cf6;"></div>
                    <span class="stat-icon-bg">👤</span>
                    <div class="flex-1">
                        <p class="stat-label text-purple-700">Registered Users</p>
                        <p class="stat-number">{{ $totalUsers }}</p>
                        <p class="stat-sub text-purple-600">{{ $newUsers }} new this month</p>
                    </div>
                </div>
            </div>

            <!-- 4. Low Stock Items -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-red card-3d">
                    <div class="stat-accent-line" style="background:#ef4444;"></div>
                    <span class="stat-icon-bg">⚠️</span>
                    <div class="flex-1">
                        <p class="stat-label text-red-700">Low Stock Items</p>
                        <p class="stat-number">{{ $lowStockItems }}</p>
                        <p class="stat-sub text-red-600">Need restock soon</p>
                    </div>
                </div>
            </div>

            <!-- 5. Total Variants -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-amber card-3d">
                    <div class="stat-accent-line" style="background:#f59e0b;"></div>
                    <span class="stat-icon-bg">🔶</span>
                    <div class="flex-1">
                        <p class="stat-label text-amber-700">Total Variants</p>
                        <p class="stat-number">{{ $totalVariants }}</p>
                        <p class="stat-sub text-amber-600">Product variations</p>
                    </div>
                </div>
            </div>

            <!-- 6. Total Inventory -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-cyan card-3d">
                    <div class="stat-accent-line" style="background:#06b6d4;"></div>
                    <span class="stat-icon-bg">📊</span>
                    <div class="flex-1">
                        <p class="stat-label text-cyan-700">Total Inventory</p>
                        <p class="stat-number">{{ number_format($totalInventory) }}</p>
                        <p class="stat-sub text-cyan-600">Units in stock</p>
                    </div>
                </div>
            </div>

            <!-- 7. Inventory Value -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-indigo card-3d">
                    <div class="stat-accent-line" style="background:#6366f1;"></div>
                    <span class="stat-icon-bg">💰</span>
                    <div class="flex-1">
                        <p class="stat-label text-indigo-700">Inventory Value</p>
                        <p class="stat-number">₱{{ number_format($inventoryValue, 0) }}</p>
                        <p class="stat-sub text-indigo-600">Total asset value</p>
                    </div>
                </div>
            </div>

            <!-- 8. Out of Stock -->
            <div class="stat-card-wrapper">
                <div class="stat-card stat-rose card-3d">
                    <div class="stat-accent-line" style="background:#f43f5e;"></div>
                    <span class="stat-icon-bg">🚫</span>
                    <div class="flex-1">
                        <p class="stat-label text-rose-700">Out of Stock</p>
                        <p class="stat-number">{{ $outOfStock }}</p>
                        <p class="stat-sub text-rose-600">Unavailable items</p>
                    </div>
                </div>
            </div>

        </div>
        <!-- ========== END STATS CARDS ========== -->


        <!-- ========== CHARTS ROW ========== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Revenue Chart -->
            <div class="card-3d bg-white rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Revenue Overview</h3>
                    <p class="text-xs text-gray-500 mt-2">Last 7 days sales</p>
                </div>
                <div class="p-5 chart-container">
                    <canvas id="revenueChart" height="220"></canvas>
                </div>
            </div>

            <!-- Order Status Distribution -->
            <div class="card-3d bg-white rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Order Status</h3>
                    <p class="text-xs text-gray-500 mt-2">Current order distribution</p>
                </div>
                <div class="p-5 chart-container">
                    <canvas id="statusChart" height="220"></canvas>
                </div>
            </div>

        </div>
        <!-- ========== END CHARTS ========== -->


        <!-- ========== QUICK ACTIONS (distinct colors) ========== -->
        <div>
            <h2 class="section-header text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.products.index') }}" class="btn-action btn-green text-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Product
                </a>
                <a href="{{ route('admin.categories.index') }}" class="btn-action btn-blue text-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Add Category
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn-action btn-amber text-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Manage Orders
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn-action btn-purple text-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manage Users
                </a>
            </div>
        </div>


        <!-- ========== LOWER GRID: Recent Orders + Low Stock Alerts ========== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Recent Orders Table (clearer separators) -->
            <div class="card-3d bg-white rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto custom-scroll">
                    <table class="orders-table w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr class="table-row-3d">
                                <td class="text-sm font-semibold text-gray-900">#{{ $order->order_id }}</td>
                                <td class="text-sm text-gray-600">{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="text-sm font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="status {{ $order->status }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">No orders yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Alerts (no max-height, all items visible) -->
            <div class="card-3d bg-white rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Low Stock Alerts</h3>
                </div>
                <div class="p-4 space-y-2 low-stock-container">
                    @forelse($lowStockProducts as $product)
                    <div class="alert-item-3d">
                        <div class="flex justify-between items-start">
                            <div>
                                <strong class="block text-gray-800 font-bold">{{ $product->product_name }}</strong>
                                <span class="text-sm text-gray-500">{{ $product->variant_size }} / {{ $product->variant_color }}</span>
                            </div>
                            <span class="text-xs font-bold text-red-600 bg-red-100 px-2.5 py-1 rounded-full whitespace-nowrap ml-2">
                                Only {{ $product->available_stock }} left
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium text-gray-500">All stock levels are healthy!</p>
                        <p class="text-sm text-gray-400">No items need restocking.</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
        <!-- ========== END LOWER GRID ========== -->

    </div>
    <!-- ========== END DASHBOARD WRAPPER ========== -->


    <!-- ========== CHARTS SCRIPT ========== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ---- Revenue Chart ----
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: @json($chartData),
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.06)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#b91c1c'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // ---- Status Chart ----
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($statusLabels),
                    datasets: [{
                        data: @json($statusCountsData),
                        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 16,
                                font: { size: 11, weight: '600' }
                            }
                        }
                    },
                    cutout: '62%'
                }
            });

        });
    </script>
@endsection