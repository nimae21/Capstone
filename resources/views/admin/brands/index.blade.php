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

        /* 3D Button - Red (Add Brand) */
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

        /* Small 3D Button - Red (Delete) */
        .btn-sm-red {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            background: #ef4444;
            color: white;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #dc2626;
            cursor: pointer;
            transition: all 0.05s linear;
            box-shadow: 0 3px 0 #991b1b;
            transform: translateY(-1px);
        }
        .btn-sm-red:active {
            transform: translateY(3px);
            box-shadow: 0 0px 0 #991b1b;
        }
        .btn-sm-red:hover {
            background: #dc2626;
        }

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

        /* 3D Table Row */
        .table-row {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .table-row:hover {
            transform: translateX(4px) translateY(-2px) translateZ(6px);
            background: linear-gradient(90deg, #ffffff, #fafcff);
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
        }

        /* Premium tips card (glass + gradient) */
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
        .tip-item {
            border-left: 2px solid #e2e8f0;
            transition: all 0.2s;
        }
        .tip-item:hover {
            border-left-color: #dc2626;
            background: rgba(255,255,255,0.5);
            transform: translateX(2px);
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

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="stat-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Brands</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $brands->count() }}</p>
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
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Brands</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $brands->where('brand_name', '!=', null)->count() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-gray-300 to-transparent rounded-full"></div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add Brand Form -->
            <div class="card-3d rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                    <h3 class="text-md font-bold gradient-title">Create New Brand</h3>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="brand_name" class="block text-sm font-semibold text-gray-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                        <input type="text" name="brand_name" id="brand_name" placeholder="e.g., Nike, Adidas, Puma" required
                               class="input-premium">
                    </div>
                    <button type="submit" class="btn-3d-red w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Brand
                    </button>
                </form>
            </div>

            <!-- Premium Guidelines Panel -->
            <div class="tips-premium-gradient rounded-2xl p-6 relative">
                <div class="relative z-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl shadow-inner">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold gradient-title">Brand Guidelines</h3>
                    </div>
                    <ul class="space-y-2 text-gray-600 text-sm">
                        <li class="tip-item pl-3 py-1">Use consistent, recognizable brand names.</li>
                        <li class="tip-item pl-3 py-1">Brands help customers filter products and build trust.</li>
                        <li class="tip-item pl-3 py-1">Deleting a brand will not delete products – they become unbranded.</li>
                        <li class="tip-item pl-3 py-1">Well-organized brands improve the shopping experience.</li>
                    </ul>
                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <div class="tip-3d flex items-center gap-2 text-sm p-3 -mx-1 rounded-lg transition-all">
                            <span class="font-medium text-gray-800">💡 Tip:</span>
                            <span class="text-gray-600">Add only active or relevant brands to keep the list clean.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brands Table -->
        <div class="card-3d rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold gradient-title">All Brands</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $brands->count() }} total</span>
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
                                        <button type="submit" class="btn-sm-red">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
                        <div style="margin-top: 20px;">
    {{ $brands->links() }}
</div>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection