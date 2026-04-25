@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')
@section('page-subtitle', 'Update category information')

@section('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }

body {
    background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
}

.card-3d {
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(2px);
    border: 1px solid rgba(255,255,255,0.5);
    box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
    border-radius: 1rem;
    padding: 1.5rem;
}

.gradient-title {
    font-weight: 800;
    background: linear-gradient(135deg, #000, #dc2626);
    -webkit-background-clip: text;
    color: transparent;
}

.input-premium {
    width: 100%;
    padding: 0.65rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.input-premium:focus {
    border-color: #dc2626;
    outline: none;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
}

.btn-save {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    width: 100%;
    font-weight: 600;
}
</style>
@endsection

@section('content')

<div class="max-w-xl mx-auto px-4 py-10">

    <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-red-600">
        ← Back
    </a>

    <div class="card-3d mt-6">

        <h1 class="text-2xl font-bold gradient-title mb-6">
            Edit Category
        </h1>

        <form method="POST" action="{{ route('admin.categories.update', $category->category_id) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="text-sm font-semibold">Category Name</label>
                <input type="text"
                       name="category_name"
                       value="{{ $category->category_name }}"
                       class="input-premium"
                       required>
            </div>

            <div class="mb-4">
                <label class="text-sm font-semibold">Description</label>
                <textarea name="category_description"
                          class="input-premium"
                          rows="4">{{ $category->category_description }}</textarea>
            </div>

            <button class="btn-save">
                Update Category
            </button>

        </form>

    </div>
</div>

@endsection