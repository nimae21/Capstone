@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products Management')
@section('page-subtitle', 'Manage your products here.')

@section('content')
    <h1>Products</h1>

    <form action="{{route('admin.products.store')}}" method="POST">
        @csrf
    
        <div>
            <label>Product Name:</label><br>
            <input type="text" name="product_name" required>
        </div>

        <br>

        <div>
            <label>Description:</label><br>
            <textarea name="product_description"></textarea>
        </div>

        <br>

        <div>
            <label>Category:</label><br>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Brand:</label><br>
            <select name="brand_id" required>
                <option value="">-- Select Brand --</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                @endforeach
            </select>
        </div>

        <br>

        <button type="submit">Add Product</button>
    </form>

    <hr><br>

    <ul>
        @forelse($products as $product)
            <li>
                <strong>{{ $product->product_name }}</strong> -
                {{ $product->product_description ?? 'No description' }}
                <form action="{{ route('admin.products.destroy', $product->product_id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
                <a href="{{ route('admin.products.edit', $product->product_id) }}">Edit</a>
                <br>
                Category: {{ $product->category->category_name ?? 'No Category' }}
                <br>
                Brand: {{ $product->brand->brand_name ?? 'No Brand' }}

                
            </li>
            <br>
        @empty
            <li>No products found.</li>
        @endforelse
    </ul>
@endsection