@if(session('error'))
    <p style="color:red;">{{ session('error') }}</p>
@endif

@if($errors->any())
    <ul style="color:red;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ route('checkout.place') }}" method="POST">
    @csrf

    <input type="text" name="full_name" placeholder="Full Name" value="{{ old('full_name') }}" required>
    
    <input type="text" name="street" placeholder="Street" value="{{ old('street') }}" required>
    
    <input type="text" name="barangay" placeholder="Barangay" value="{{ old('barangay') }}" required>
    
    <input type="text" name="city" placeholder="City" value="{{ old('city') }}" required>
    
    <input type="text" name="province" placeholder="Province" value="{{ old('province') }}" required>
    
    <input type="text" name="postal_code" placeholder="Postal Code" value="{{ old('postal_code') }}" required>
    
    <input type="text" name="phone_number" placeholder="Phone Number" value="{{ old('phone_number') }}" required>

    <button type="submit">Place Order</button>
</form>