@extends('layouts.admin')

@section('title', 'Brands')
@section('page-title', 'Brands Management')
@section('page-subtitle', 'Manage your product brands here.')

@section('content')
    <h1>Brands</h1>

    <form action="{{ route('admin.brands.store') }}" method="POST">
        @csrf

        <div>
            <label>Brand Name:</label><br>
            <input type="text" name="brand_name" required>
        </div>

        <br>

        <br>

        <button type="submit">Add Brand</button>
    </form>

    <hr><br>

    <ul>
        @forelse($brands as $brand)
            <li>
                {{ $brand->brand_name }}

                <form action="{{ route('admin.brands.destroy', $brand->brand_id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </li>
        @empty
            <li>No brands found.</li>
        @endforelse
    </ul>
@endsection