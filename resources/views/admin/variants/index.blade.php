@extends('layouts.admin')

@section('title', 'Product Variants')
@section('page-title', 'Manage Variants for ' . $product->product_name)
@section('page-subtitle', 'Add, view, or delete product variants.')

@section('content')

<h2>{{ $product->product_name }} Variants</h2>

<!-- Add New Variant -->
<form action="{{ route('admin.products.variants.store', $product->product_id) }}" method="POST">
    @csrf
    <div>
        <label>Size:</label>
        <input type="text" name="size" required>
    </div>
    <div>
        <label>Color:</label>
        <input type="text" name="color" required>
    </div>
    <button type="submit">Add Variant</button>
</form>

<hr>

<!-- List of Variants -->
<ul>
    @forelse($variants as $variant)
        <li>
            Size: {{ $variant->size }}, Color: {{ $variant->color }}
            <form action="{{ route('admin.products.variants.destroy', [$product->product_id, $variant->productvariant_id]) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </li>
    @empty
        <li>No variants found.</li>
    @endforelse
</ul>

@endsection