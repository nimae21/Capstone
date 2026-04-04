@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products Management')
@section('page-subtitle', 'Manage your products here.')

@section('content')
<h1>Products</h1>

<!-- Add Product Form -->
<form action="{{ route('admin.products.store') }}" method="POST">
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
            Category: {{ $product->category->category_name ?? 'No Category' }} <br>
            Brand: {{ $product->brand->brand_name ?? 'No Brand' }}

            <!-- ====== Variants Section ====== -->
            <div style="margin-top:10px; border:1px solid #ccc; padding:10px;">
                <h4>Variants</h4>

                <!-- Add Variant Form -->
                <form action="{{ route('admin.variants.store', $product->product_id) }}" method="POST" style="margin-bottom:10px;">
                    @csrf
                    <input type="text" name="size" placeholder="Size" required>
                    <input type="text" name="color" placeholder="Color" required>
                    <button type="submit">Add Variant</button>
                </form>

                <!-- Variants Table -->
                @if($product->variants->count())
                    <table border="1" cellpadding="5" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variants as $variant)
                                <tr>
                                    <td>{{ $variant->size }}</td>
                                    <td>{{ $variant->color }}</td>
                                    <td>
                                        <a href="{{ route('admin.variants.edit', [$product->product_id, $variant->product_variant_id]) }}">Edit</a>
                                        <form action="{{ route('admin.variants.destroy', [$product->product_id, $variant->product_variant_id]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No variants found.</p>
                @endif
            </div>
            <!-- ====== End Variants Section ====== -->
        </li>
        <br>
    @empty
        <li>No products found.</li>
    @endforelse
</ul>
@endsection