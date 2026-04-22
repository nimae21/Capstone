@extends('layouts.admin')

@section('title', 'Edit Variant')

@section('content')
<h1>Edit Variant</h1>

<form action="{{ route('admin.variants.update', $variant->product_variant_id) }}" method="POST">
    @csrf
    @method('PUT')

    <label>Size</label>
    <input type="text" name="size" value="{{ old('size', $variant->size) }}">

    <label>Color</label>
    <input type="text" name="color" value="{{ old('color', $variant->color) }}">

    <button type="submit">Update Variant</button>
</form>
@endsection