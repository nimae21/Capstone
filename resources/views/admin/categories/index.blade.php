@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories Management')
@section('page-subtitle', 'Manage your product categories here.')

@section('content')
    <h1>Categories</h1>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf

        <div>
            <label>Category Name:</label><br>
            <input type="text" name="category_name" required>
        </div>

        <br>

        <div>
            <label>Description:</label><br>
            <textarea name="category_description"></textarea>
        </div>

        <br>

        <button type="submit">Add Category</button>
    </form>

    <hr><br>

    <ul>
        @forelse($categories as $category)
            <li>
                {{ $category->category_name }} - {{ $category->category_description }}

                <form action="{{ route('admin.categories.destroy', $category->category_id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </li>
        @empty
            <li>No categories found.</li>
        @endforelse
    </ul>
@endsection