@extends('layouts.admin')

@section('title', 'Edit Shoe Type')
@section('page-title', 'Edit Shoe Type')
@section('page-subtitle', 'Update shoe type details, order, and status.')

@section('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%); }

        .card-3d {
            background: rgba(255,255,255,0.96);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
        }

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .btn-3d-red {
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
            box-shadow: 0 8px 0 #991b1b, 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .btn-3d-red:active { transform: translateY(6px); box-shadow: 0 2px 0 #991b1b; }
        .btn-3d-red:hover { background: linear-gradient(135deg, #dc2626, #b91c1c); }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: transparent;
            color: #4b5563;
            font-weight: 600;
            padding: 0.7rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            text-decoration: none;
        }
        .btn-outline:hover { background: #f9fafb; border-color: #dc2626; color: #dc2626; }

        .input-premium {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
        }
        .input-premium:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
        }

        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
        }
    </style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto py-10 relative z-10">
    <div class="card-3d rounded-2xl p-6 md:p-8">

        <div class="flex items-center gap-2 mb-6">
            <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
            <h2 class="text-xl font-bold gradient-title">Edit Shoe Type</h2>
        </div>

        <a href="{{ route('admin.shoe-types.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-red-600 transition-all mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Shoe Types
        </a>

        <form action="{{ route('admin.shoe-types.update', $shoeType->shoe_type_id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Shoe Type Name <span class="text-red-500">*</span></label>
                <input type="text" name="shoe_type_name"
                       value="{{ old('shoe_type_name', $shoeType->shoe_type_name) }}"
                       class="input-premium" required>
                @error('shoe_type_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="input-premium resize-none">{{ old('description', $shoeType->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Display Order</label>
                <input type="number" name="display_order" min="0"
                       value="{{ old('display_order', $shoeType->display_order) }}"
                       class="input-premium" required>
                <p class="text-xs text-gray-400 mt-1">Lower numbers appear first on the storefront.</p>
                @error('display_order')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="toggle-row">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Active</p>
                    <p class="text-xs text-gray-400">Inactive shoe types won't appear in product filters.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $shoeType->is_active) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-red-600 transition-colors relative">
                        <div class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('admin.shoe-types.index') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-3d-red">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Update Shoe Type
                </button>
            </div>
        </form>
    </div>
</div>
@endsection