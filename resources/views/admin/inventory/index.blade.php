@extends('layouts.admin')

@section('title', 'Inventory')
@section('page-title', 'Inventory Management')
@section('page-subtitle', 'Track stock levels, monitor low inventory, and manage product availability.')

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

        /* Stats Cards */
        .stat-card {
            background: linear-gradient(115deg, #ffffff, #fefefe);
            border-radius: 1rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #ef4444, #dc2626);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Table Styles */
        .inventory-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .inventory-table thead th {
            background: #f8fafc;
            padding: 1rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        .inventory-table tbody tr {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border-bottom: 1px solid #f1f5f9;
        }
        .inventory-table tbody tr:hover {
            background: linear-gradient(90deg, #fafcff, #ffffff);
            transform: translateX(4px);
            box-shadow: 0 4px 12px -6px rgba(0,0,0,0.08);
        }

        .inventory-table td {
            padding: 1rem 1rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .status-badge.in-stock {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.low-stock {
            background: #fef3c7;
            color: #92400e;
        }
        .status-badge.out-stock {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Stock Level Bar */
        .stock-bar-container {
            width: 100px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
        }
        .stock-bar {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
        .stock-bar.high { background: #10b981; }
        .stock-bar.medium { background: #f59e0b; }
        .stock-bar.low { background: #ef4444; }

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

        /* Search & Filter */
        .search-input {
            transition: all 0.2s ease;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.625rem 1rem;
            width: 100%;
            max-width: 300px;
        }
        .search-input:focus {
            transform: translateY(-1px);
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
            outline: none;
        }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 relative z-10">

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Variants -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Variants</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $totalProducts }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total stock keeping units</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-blue-300 to-transparent rounded-full"></div>
        </div>

        <!-- Low Stock Alert -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Low Stock</p>
                    <p class="text-3xl font-black text-yellow-600 mt-2">{{ $lowStock }}</p>
                    <p class="text-xs text-gray-400 mt-1">Items below 5 units</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-yellow-50 to-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-yellow-300 to-transparent rounded-full"></div>
        </div>

        <!-- Out of Stock -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Out of Stock</p>
                    <p class="text-3xl font-black text-red-600 mt-2">{{ $outOfStock }}</p>
                    <p class="text-xs text-gray-400 mt-1">Items with zero stock</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-red-50 to-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-red-300 to-transparent rounded-full"></div>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-2">
            <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
            <h3 class="text-lg font-bold gradient-title">Current Inventory</h3>
        </div>
        
        <div class="flex gap-3">
            <input type="text" id="searchInput" placeholder="Search by product, size, or color..." class="search-input">
            <select id="statusFilter" class="search-input max-w-[150px]">
                <option value="all">All Status</option>
                <option value="in-stock">In Stock</option>
                <option value="low-stock">Low Stock</option>
                <option value="out-stock">Out of Stock</option>
            </select>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card-3d rounded-2xl overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="inventory-table" id="inventoryTable">
                <thead>
                    <tr>
                        <th class="text-left">Product</th>
                        <th class="text-left">Variant</th>
                        <th class="text-center">Stock Level</th>
                        <th class="text-center">Sold</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                        @php
                            $statusClass = 'in-stock';
                            $statusText = 'IN STOCK';
                            $barClass = 'high';
                            $barWidth = 100;
                            
                            if ($item->available_stock <= 0) {
                                $statusClass = 'out-stock';
                                $statusText = 'OUT OF STOCK';
                                $barClass = 'low';
                                $barWidth = 0;
                            } elseif ($item->available_stock <= 5) {
                                $statusClass = 'low-stock';
                                $statusText = 'LOW STOCK';
                                $barClass = 'low';
                                $barWidth = ($item->available_stock / 5) * 100;
                            } elseif ($item->available_stock <= 20) {
                                $barClass = 'medium';
                                $barWidth = ($item->available_stock / 50) * 100;
                            } else {
                                $barWidth = min(100, ($item->available_stock / 100) * 100);
                            }
                            
                            $barWidth = max(5, min(100, $barWidth));
                        @endphp
                        
                        <tr data-status="{{ $statusClass }}" data-name="{{ strtolower($item->product_name . ' ' . $item->size . ' ' . $item->color) }}">
                            <td class="font-semibold text-gray-800">
                                {{ $item->product_name }}
                            </td>
                            <td class="text-gray-600">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $item->size }}</span>
                                    <span class="text-gray-400">/</span>
                                    <span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">{{ $item->color }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-sm font-semibold">{{ $item->total_stock }}</span>
                                    <div class="stock-bar-container">
                                        <div class="stock-bar {{ $barClass }}" style="width: {{ $barWidth }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center text-gray-600">{{ $item->total_sold }}</td>
                            <td class="text-center font-bold text-gray-800">{{ $item->available_stock }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $statusClass }}">
                                    @if($statusClass == 'in-stock')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @elseif($statusClass == 'low-stock')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                No inventory data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Call to Action -->
    @if($lowStock > 0)
    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-2xl p-5 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">Low Stock Alert</h4>
                    <p class="text-sm text-gray-600">You have {{ $lowStock }} items that need restocking soon.</p>
                </div>
            </div>
            <button onclick="filterLowStock()" class="px-4 py-2 bg-yellow-500 text-white rounded-lg font-semibold text-sm hover:bg-yellow-600 transition">
                View Low Stock Items
            </button>
        </div>
    </div>
    @endif
</div>

<script>
    // Search and Filter Functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('#inventoryTable tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        tableRows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowName = row.getAttribute('data-name') || '';
            
            let matchesSearch = rowName.includes(searchTerm);
            let matchesStatus = statusValue === 'all' || rowStatus === statusValue;
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', filterTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }

    function filterLowStock() {
        if (statusFilter) {
            statusFilter.value = 'low-stock';
            filterTable();
            document.getElementById('lowStockAlert')?.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
@endsection