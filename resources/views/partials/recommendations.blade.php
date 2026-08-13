@if($recommendations->isNotEmpty())
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center gap-2 mb-6">
        <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
        <h2 class="text-2xl font-bold bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">
            Recommended For You
        </h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($recommendations as $product)
            <a href="{{ route('product.show', $product->product_id) }}" class="group">
                <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-lg transition-all">
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        @if($product->images->first())
                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                 alt="{{ $product->product_name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <i class="fas fa-shoe-prints text-4xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $product->brand->brand_name ?? '' }}</p>
                        <h3 class="font-semibold text-gray-900 text-sm mt-1 truncate">{{ $product->product_name }}</h3>
                        <p class="text-red-600 font-bold mt-1">₱{{ number_format($product->display_price, 2) }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif