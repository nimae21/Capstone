@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')
@section('page-subtitle', 'Update product details here.')

@section('content')
<form action="{{ route('admin.products.update', $product->product_id) }}" method="POST">
    @csrf
    @method('PUT')

    <div>
        <label>Product Name:</label>
        <input type="text" name="product_name" value="{{ $product->product_name }}" required>
    </div>

    <div>
        <label>Description:</label>
        <textarea name="product_description">{{ $product->product_description }}</textarea>
    </div>

    <div>
        <label>Category:</label>
        <select name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->category_id }}" {{ $category->category_id == $product->category_id ? 'selected' : '' }}>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Brand:</label>
        <select name="brand_id" required>
            @foreach($brands as $brand)
                <option value="{{ $brand->brand_id }}" {{ $brand->brand_id == $product->brand_id ? 'selected' : '' }}>
                    {{ $brand->brand_name }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit">Update Product</button>
</form>
@endsection