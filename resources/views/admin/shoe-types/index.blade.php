@extends('layouts.admin')

@section('title', 'Shoe Types')
@section('page-title', 'Shoe Types')
@section('page-subtitle', 'Manage shoe type categories and their display order.')

@section('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
        }

        .card-3d {
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .card-3d:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
        }

        .gradient-title {
            font-weight: 900 !important;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2.5rem;
        }

        .section-header {
            font-weight: 800 !important;
            font-size: 1.15rem !important;
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

        .stat-card {
            position: relative;
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            min-height: 120px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .stat-card .stat-icon-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 6rem;
            opacity: 0.18;
            pointer-events: none;
            transform: rotate(8deg);
        }
        .stat-card .stat-number {
            font-weight: 900 !important;
            font-size: 2.2rem !important;
        }
        .stat-card .stat-label {
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            text-transform: uppercase;
            opacity: 0.75;
        }
        .stat-card .stat-accent-line {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
        }
        .stat-blue { background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%); border-left: 4px solid #3b82f6; }
        .stat-blue .stat-number { color: #1e40af; }
        .stat-green { background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%); border-left: 4px solid #10b981; }
        .stat-green .stat-number { color: #065f46; }
        .stat-red { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-left: 4px solid #ef4444; }
        .stat-red .stat-number { color: #991b1b; }

        .input-compact {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 0.875rem;
        }
        .input-compact:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
        }

        .btn-create-3d {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-weight: 700;
            padding: 0.65rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 0 #991b1b, 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .btn-create-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #991b1b, 0 8px 16px -4px rgba(220, 38, 38, 0.2);
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
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-sm-blue { background: #3b82f6; color: white; box-shadow: 0 2px 0 #1d4ed8; }
        .btn-sm-blue:hover { background: #2563eb; color: white; }
        .btn-sm-red { background: #ef4444; color: white; box-shadow: 0 2px 0 #b91c1c; }
        .btn-sm-red:hover { background: #dc2626; color: white; }

        .type-item {
            position: relative;
            background: white;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin-bottom: 0.75rem;
            border: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }
        .type-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 20px -8px rgba(59, 130, 246, 0.12);
            transform: translateY(-2px);
        }
        .type-order-badge {
            width: 34px;
            height: 34px;
            border-radius: 0.6rem;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">

    <div class="mb-6">
        <h1 class="gradient-title">Shoe Types</h1>
        <p class="text-gray-500 text-sm mt-1 flex items-center gap-2">
            <i class="fas fa-shoe-prints text-gray-400"></i>
            Manage shoe type categories and their display order.
        </p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl mb-6 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="stat-card stat-blue card-3d">
            <div class="stat-accent-line" style="background:#3b82f6;"></div>
            <span class="stat-icon-bg">👟</span>
            <div class="flex-1">
                <p class="stat-label">Total Types</p>
                <p class="stat-number">{{ number_format($totalTypes) }}</p>
            </div>
        </div>
        <div class="stat-card stat-green card-3d">
            <div class="stat-accent-line" style="background:#10b981;"></div>
            <span class="stat-icon-bg">✅</span>
            <div class="flex-1">
                <p class="stat-label">Active Types</p>
                <p class="stat-number">{{ number_format($activeTypes) }}</p>
            </div>
        </div>
        <div class="stat-card stat-red card-3d">
            <div class="stat-accent-line" style="background:#ef4444;"></div>
            <span class="stat-icon-bg">🚫</span>
            <div class="flex-1">
                <p class="stat-label">Inactive Types</p>
                <p class="stat-number">{{ number_format($inactiveTypes) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8">
        <h3 class="section-header text-gray-800 mb-5">Create Shoe Type</h3>
        <form action="{{ route('admin.shoe-types.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="text" name="shoe_type_name" value="{{ old('shoe_type_name') }}"
                           placeholder="e.g., Sneakers, Boots, Sandals" required class="input-compact">
                </div>
                <div>
                    <input type="text" name="description" value="{{ old('description') }}"
                           placeholder="Description (optional)" class="input-compact">
                </div>
            </div>
            <button type="submit" class="btn-create-3d flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Add Shoe Type
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="section-header text-gray-800">All Shoe Types</h3>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                {{ $shoeTypes->total() }} total
            </span>
        </div>

        @forelse($shoeTypes as $shoeType)
            <div class="type-item">
                <div class="flex items-center gap-4">
                    <span class="type-order-badge">{{ $shoeType->display_order }}</span>
                    <div>
                        <div class="font-bold text-gray-800">{{ $shoeType->shoe_type_name }}</div>
                        @if($shoeType->description)
                            <div class="text-sm text-gray-500">{{ $shoeType->description }}</div>
                        @endif
                        @if($shoeType->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                ● Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                ● Inactive
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.shoe-types.edit', $shoeType->shoe_type_id) }}" class="btn-sm-3d btn-sm-blue">
                        <i class="fas fa-edit"></i> Edit
                    </a>

                    @if($shoeType->is_active)
                        <button type="button"
                                onclick="openDeleteModal({{ $shoeType->shoe_type_id }}, '{{ addslashes($shoeType->shoe_type_name) }}')"
                                class="btn-sm-3d btn-sm-red">
                            <i class="fas fa-ban"></i> Deactivate
                        </button>
                    @else
                        <form action="{{ route('admin.shoe-types.restore', $shoeType->shoe_type_id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn-sm-3d" style="background:#10b981;color:white;">
                                <i class="fas fa-rotate-left"></i> Activate
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-400">
                <i class="fas fa-shoe-prints text-5xl mb-3 block opacity-30"></i>
                <p class="text-lg font-medium">No shoe types yet</p>
                <p class="text-sm">Start by creating your first shoe type above.</p>
            </div>
        @endforelse

        @if(method_exists($shoeTypes, 'links'))
            <div class="mt-4 flex justify-center">
                {{ $shoeTypes->links() }}
            </div>
        @endif
    </div>

</div>

<div id="deleteModal" style="display:none;" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-md p-6 rounded-xl shadow-lg">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-800">Confirm Deactivate</h2>
        </div>
        <p class="text-sm text-gray-600" id="deleteModalMessage"></p>
        <form id="deleteShoeTypeForm" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2">
                <i class="fas fa-ban"></i> Yes, Deactivate
            </button>
        </form>
    </div>
</div>

<script>
    function openDeleteModal(id, name) {
        document.getElementById('deleteShoeTypeForm').action = '/admin/shoe-types/' + id;
        document.getElementById('deleteModalMessage').textContent =
            `Deactivate shoe type "${name}"? You can activate it again later.`;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.bg-emerald-50, .bg-red-50').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity .5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
</script>
@endsection