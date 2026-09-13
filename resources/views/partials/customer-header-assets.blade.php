@once
<link rel="stylesheet" href="{{ asset('css/customer-header.css') . '?v=' . filemtime(public_path('css/customer-header.css')) }}">
<script src="{{ asset('js/product-search.js') . '?v=' . filemtime(public_path('js/product-search.js')) }}" defer></script>
@endonce
