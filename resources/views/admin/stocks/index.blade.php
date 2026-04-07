@extends('layouts.admin')

@section('title', 'Manage Stocks')

@section('page-title', 'Stock Management')
@section('page-subtitle', 'Manage stocks for a specific product variant')

@section('content')
<h1>Manage Stocks for Variant: {{ $variant->size }} / {{ $variant->color }}</h1>

<!-- ===== Add Stock Form ===== -->
<form action="{{ route('admin.stocks.store', $variant->product_variant_id) }}" method="POST" style="margin-bottom:20px;">
    @csrf
    <div>
        <label>Quantity:</label>
        <input type="number" name="quantity" placeholder="Quantity" required>
    </div>
    <div>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
    </div>
    <div>
        <label>Deliver Date:</label>
        <input type="date" name="deliver_date" placeholder="Deliver Date" required>
    </div>
    <br>
    <button type="submit">Add Stock</button>
</form>

<!-- ===== Stocks Table ===== -->
@if($stocks->count())
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Deliver Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $stock)
            <tr>
                <td>{{ $stock->stock_id }}</td>
                <td>{{ $stock->quantity }}</td>
                <td>{{ number_format($stock->price, 2) }}</td>
                <td>{{ $stock->deliver_date }}</td>
                <td>
                    <a href="{{ route('admin.stocks.edit', $stock->stock_id) }}">Edit</a>
                    <form action="{{ route('admin.stocks.destroy', $stock->stock_id) }}" method="POST" style="display:inline;">
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
    <p>No stock entries found for this variant.</p>
@endif

<!-- ===== Back to Variant Page ===== -->
<br>
<a href="{{ route('admin.products.variants.index', $variant->product_id) }}">← Back to Variants</a>
@endsection