@extends('layouts.app')

@section('title', 'Edit Address')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('addresses.index') }}" class="text-red-500 hover:text-red-600 font-semibold mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Back to Addresses
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Address</h1>
            <p class="text-gray-600 mt-2">Update your delivery address details</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form action="{{ route('addresses.update', $address->address_id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="full_name" 
                        id="full_name"
                        value="{{ old('full_name', $address->full_name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @error('full_name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                    <input 
                        type="tel" 
                        name="phone_number" 
                        id="phone_number"
                        value="{{ old('phone_number', $address->phone_number) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @error('phone_number')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Street -->
                <div>
                    <label for="street" class="block text-sm font-semibold text-gray-700 mb-2">Street Address <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="street" 
                        id="street"
                        value="{{ old('street', $address->street) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @error('street')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Barangay -->
                <div>
                    <label for="barangay" class="block text-sm font-semibold text-gray-700 mb-2">Barangay <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="barangay" 
                        id="barangay"
                        value="{{ old('barangay', $address->barangay) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @error('barangay')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- City and Province in Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City/Municipality <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="city" 
                            id="city"
                            value="{{ old('city', $address->city) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        >
                        @error('city')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Province -->
                    <div>
                        <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Province <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="province" 
                            id="province"
                            value="{{ old('province', $address->province) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        >
                        @error('province')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">Postal Code <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="postal_code" 
                        id="postal_code"
                        value="{{ old('postal_code', $address->postal_code) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @error('postal_code')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Set as Default -->
                <div class="flex items-center gap-3">
                    <input 
                        type="checkbox" 
                        name="is_default" 
                        id="is_default"
                        value="1"
                        {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                        class="w-4 h-4 text-red-500 rounded focus:ring-2 focus:ring-red-500"
                    >
                    <label for="is_default" class="text-sm font-medium text-gray-700">
                        <i class="fas fa-star text-red-500 mr-1"></i> Set as default delivery address
                    </label>
                </div>

                <!-- Form Actions -->
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
