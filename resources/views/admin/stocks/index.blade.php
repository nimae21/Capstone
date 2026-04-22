@extends('layouts.admin')

@section('title', 'Manage Stocks')
@section('page-title', 'Stock Management')
@section('page-subtitle', 'Manage stocks for a specific product variant')

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

        /* 3D Card effect - premium glassmorphism */
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

        /* 3D Button - Red (Add Stock) */
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative z-10">
        <!-- Back to Variants Link -->
        <div class="mb-6">
            <a href="{{ route('admin.products.variants.index', $variant->product_id) }}" class="back-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Variants
            </a>
        </div>

        <!-- Variant Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold gradient-title">Stock Management</h1>
            <p class="text-gray-500 mt-1">Manage stocks for variant: <span class="font-semibold text-gray-700">{{ $variant->size }} / {{ $variant->color }}</span></p>
        </div>

        <!-- Two Column Layout: Add Stock Form + Quick Tip -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Add Stock Form -->
            <div class="card-3d rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                    <h3 class="text-md font-bold gradient-title">Add New Stock Entry</h3>
                </div>
                <form action="{{ route('admin.stocks.store', $variant->product_variant_id) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" placeholder="e.g., 100" required
                               class="input-premium">
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="price" id="price" placeholder="e.g., 2,500" required
                               class="input-premium">
                    </div>
                    <div>
                        <label for="deliver_date" class="block text-sm font-semibold text-gray-700 mb-1">Deliver Date <span class="text-red-500">*</span></label>
                        <input type="date" name="deliver_date" id="deliver_date" required
                               class="input-premium">
                    </div>
                    <button type="submit" class="btn-3d-red w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Stock
                    </button>
                </form>
            </div>

            <!-- Quick Stats / Tip Panel -->
            <div class="tips-premium-gradient rounded-2xl p-6 relative" style="background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,250,252,0.98)); backdrop-filter: blur(8px); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 20px 35px -12px rgba(0,0,0,0.08);">
                <div class="relative z-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl shadow-inner">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="text-lg font-bold gradient-title">Stock Summary</h3>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/50 rounded-xl mb-3">
                        <span class="text-sm text-gray-600">Total Stock Entries</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $stocks->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/50 rounded-xl mb-3">
                        <span class="text-sm text-gray-600">Total Quantity</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $stocks->sum('quantity') }}</span>
                    </div>
                    <div class="tip-3d flex items-center gap-2 text-sm p-3 rounded-lg transition-all mt-4">
                        <span class="font-medium text-gray-800">💡 Tip:</span>
                        <span class="text-gray-600">Each stock entry represents a delivery batch. Track quantities and prices per batch.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stocks Table -->
        <div class="card-3d rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold gradient-title">Stock Entries</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $stocks->count() }} entry(ies)</span>
            </div>
            <div class="overflow-x-auto custom-scroll">
                @if($stocks->count())
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Quantity</th>
                                <th class="px-6 py-3">Price</th>
                                <th class="px-6 py-3">Deliver Date</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($stocks as $stock)
                                <tr class="table-row-3d">
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $stock->stock_id }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ $stock->quantity }}</td>
                                    <td class="px-6 py-3 text-gray-600">${{ number_format($stock->price, 2) }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $stock->deliver_date }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex gap-2 justify-end">
                                            <a href="{{ route('admin.stocks.edit', $stock->stock_id) }}" class="btn-sm-3d btn-sm-blue">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.stocks.destroy', $stock->stock_id) }}" method="POST" onsubmit="return confirm('Delete this stock entry? This action cannot be undone.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-sm-3d btn-sm-red">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12 text-gray-500">
                        No stock entries found for this variant. Add your first stock entry using the form.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection