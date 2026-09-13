@extends('layouts.app')

@section('title', 'Edit Address')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('addresses.index') }}" class="text-red-500 hover:text-red-600 font-semibold mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Back to Addresses
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Address</h1>
            <p class="text-gray-600 mt-2">Update your delivery address details</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-8">
            <form action="{{ route('addresses.update', $address->address_id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                @include('addresses.fields', ['address' => $address])

                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i> Update Address
                    </button>
                    <a href="{{ route('addresses.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/address-selector.js')
@endpush
