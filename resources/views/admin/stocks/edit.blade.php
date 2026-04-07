@extends('layouts.admin')

@section('title', 'Edit Stock')

@section('content')
<h1>Edit Stock for Variant: {{ $stock->variant->size }} / {{ $stock->variant->color }}</h1>

<form action="{{ route('admin.stocks.update', $stock->stock_id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="number" name="quantity" value="{{ $stock->quantity }}" required>
    <input type="number" step="0.01" name="price" value="{{ $stock->price }}" required>
    <input type="date" name="deliver_date" value="{{ $stock->deliver_date }}" required>
    <button type="submit">Update Stock</button>
</form>
@endsection