@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')
@section('page-subtitle', 'Update category details here.')

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
            transform: translateY(-6px) translateZ(12px) scale(1.01);
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

        /* 3D Input/Textarea focus */
        .input-3d, textarea.input-3d {
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02), 0 1px 1px rgba(0,0,0,0.01);
        }
        .input-3d:focus, textarea.input-3d:focus {
            transform: translateZ(2px);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05), inset 0 1px 2px rgba(0,0,0,0.02);
            border-color: #9ca3af;
            outline: none;
        }

        /* Back link subtle */
        .back-link {
            transition: all 0.15s ease;
        }
        .back-link:hover {
            transform: translateX(-2px);
        }
    </style>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back to Categories Link -->
        <div class="mb-6">
            <a href="{{ route('admin.categories.index') }}" class="back-link inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Categories
            </a>
        </div>

        <!-- Edit Category Form Card -->
        <div class="card-3d bg-white rounded-xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 tracking-tight">Edit Category</h3>
            </div>

            <form action="{{ route('admin.categories.update', $categories->category_id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Category Name -->
                <div>
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-gray-500">*</span></label>
                    <input type="text" name="category_name" id="category_name" value="{{ old('category_name', $categories->category_name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800">
                </div>

                <!-- Description -->
                <div>
                    <label for="category_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="category_description" id="category_description" rows="4"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-gray-400 input-3d transition bg-white text-gray-800">{{ old('category_description', $categories->category_description) }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="btn-3d-white w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection