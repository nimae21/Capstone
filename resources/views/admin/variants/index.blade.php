@extends('layouts.admin')

@section('title', 'Manage Variants')
@section('page-title', 'Manage Variants')
@section('page-subtitle', 'Manage variants for this product.')

@section('content')
<h1>Manage Variants for {{ $product->product_name }}</h1>

<br>

<!-- Add Variant Form -->
<form action="{{ route('admin.products.variants.store', $product->product_id) }}" method="POST">
    @csrf

    <div>
        <label>Size:</label><br>
        <input type="text" name="size" placeholder="Enter size" required>
    </div>
    <br>

    <div>
        <label>Color:</label><br>
        <input type="text" name="color" placeholder="Enter color" required>
    </div>
    <br>

    <button type="submit">Add Variant</button>
</form>

<hr><br>

<!-- Variants Table -->
@if($product->variants->count())
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Size</th>
                <th>Color</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($product->variants as $variant)
                <tr>
                    <td>{{ $variant->product_variant_id }}</td>
                    <td>{{ $variant->size }}</td>
                    <td>{{ $variant->color }}</td>
                    <td>
                        <a href="{{ route('admin.variants.edit', $variant->product_variant_id) }}">Edit</a>

                        <form action="{{ route('admin.variants.destroy', $variant->product_variant_id) }}" method="POST" style="display:inline;">
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
    <p>No variants found for this product.</p>
@endif

<br>
<a href="{{ route('admin.products.index') }}">← Back to Products</a>
@endsection