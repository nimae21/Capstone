                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="full_name" 
                        id="full_name"
                        placeholder="e.g., Juan Dela Cruz"
                        value="{{ old('full_name') }}"
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
                        placeholder="e.g., +63 912 345 6789"
                        value="{{ old('phone_number') }}"
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
                        placeholder="e.g., 123 Main Street"
                        value="{{ old('street') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                    @error('street')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
<button
    type="button"
    id="locateMe"
    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">

    📍 Use My Current Location

</button>
<p id="locationStatus" class="mt-2 text-sm text-gray-500" role="status" aria-live="polite"></p>

                <div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">
        Pin Your Location
    </label>

    <div
        id="map"
        class="w-full h-96 rounded-lg border border-gray-300">
    </div>
</div>
<input
    type="hidden"
    name="latitude"
    id="latitude" value="{{ old('latitude') }}">

<input
    type="hidden"
    name="longitude"
    id="longitude" value="{{ old('longitude') }}">
    <button
    type="button"
    id="findLocation"
    class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
    📍 Find Address on Map
</button>

    <!-- Region -->
    <div>
        <label for="region" class="block text-sm font-semibold text-gray-700 mb-2">Region <span class="text-red-500">*</span></label>
        <select
            id="region"
            name="region"
            required
            data-default="{{ old('region') }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Select Region</option>
        </select>
        @error('region')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Province -->
    <div>
        <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Province <span class="text-red-500">*</span></label>
        <select
            name="province"
            id="province"
            required
            data-default="{{ old('province') }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            <option value="">Select Province</option>
        </select>
        @error('province')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- City -->
    <div>
        <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City/Municipality <span class="text-red-500">*</span></label>
        <select
            name="city"
            id="city"
            required
            data-default="{{ old('city') }}"
            disabled
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            <option value="">Select City/Municipality</option>
        </select>
        @error('city')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Barangay -->
    <div>
        <label for="barangay" class="block text-sm font-semibold text-gray-700 mb-2">Barangay <span class="text-red-500">*</span></label>
        <select
            name="barangay"
            id="barangay"
            required
            data-default="{{ old('barangay') }}"
            disabled
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            <option value="">Select Barangay</option>
        </select>
        @error('barangay')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

                <!-- Postal Code -->
                <div>
                    <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">Postal Code <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="postal_code" 
                        id="postal_code"
                        placeholder="e.g., 1000"
                        value="{{ old('postal_code') }}"
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
                        {{ old('is_default') ? 'checked' : '' }}
                        class="w-4 h-4 text-red-500 rounded focus:ring-2 focus:ring-red-500"
                    >
                    <label for="is_default" class="text-sm font-medium text-gray-700">
                        <i class="fas fa-star text-red-500 mr-1"></i> Set as default delivery address
                    </label>
                </div>

